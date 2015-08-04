<?php
/**
  * Test the Misc controller, mainly the user functions
  *
  * @author Cédric Airaud
  */

namespace SLN\RegisterBundle\Tests\Controller;

use Liip\FunctionalTestBundle\Test\WebTestCase;

/**
  * Test the Misc controller, mainly the user functions
  */
class MiscControllerTest extends WebTestCase
{
    /** Normal user to test */
    const TEST_USER      = "test-user@test.com";
    /** Password for normal user */
    const TEST_USER_PWD  = "test";

    /** User with admin rights */
    const TEST_ADMIN     = "test-admin@test.com";
    /** Password for user with admin */
    const TEST_ADMIN_PWD = "test";

    /**
     * Set-up: enable profiler
     */
    public function setUp() {
        parent::setUp();
        $this->client = static::createClient();
        $this->client->enableProfiler();
    }

    /**
     * Test the contact view.
     *
     * Test that:
     * - Form can be submitted
     * - Mail is sent with the correct content
     */
    public function testContact() {
        $this->loadFixtures(array(
            'SLN\RegisterBundle\DataFixtures\ORM\LoadUserData',
        ));

        $this->doLogin(self::TEST_USER, self::TEST_USER_PWD);

        $crawler = $this->client->request('GET', '/contact');
        $form = $crawler->selectButton('_submit')->form(array(
            "contact[subject]" => "Sujet du message",
            "contact[body]" => "Texte du message"
        ));

        $this->client->submit($form);
        $this->assertTrue($this->client->getResponse()->isRedirect());

        // Check that an email was sent
        $profile = $this->client->getProfile();
        $this->assertTrue($profile !== False);
        $mailCollector = $profile->getCollector('swiftmailer');

        $this->assertEquals(1, $mailCollector->getMessageCount());
        $collectedMessages = $mailCollector->getMessages();
        $message = $collectedMessages[0];

        $crawler = $this->client->followRedirect();
        $this->assertTrue($crawler->filter('html:contains("Votre message \'Sujet du message\'")')->count() > 0);

        // Asserting email data
        $this->assertInstanceOf('Swift_Message', $message);
        $this->assertEquals('Question du site d\'inscription', $message->getSubject());
        $this->assertEquals('slnslv@free.fr', key($message->getFrom()));
        $this->assertEquals("cairaud@gmail.com", key($message->getTo()));
        $this->assertContains(
            'Texte du message',
            $message->getBody()
        );
    }


    /**
     * Log a user, to be used with further pages
     *
     * @param string $username Username of the user to log in
     * @param string $password Password ot the user to log in
     */
    public function doLogin($username, $password) {
      $crawler = $this->client->request('GET', '/login');
      $form = $crawler->selectButton('_submit')->form(array(
        '_username'  => $username,
        '_password'  => $password,
      ));     
      $this->client->submit($form);

      $this->assertTrue($this->client->getResponse()->isRedirect());

      $crawler = $this->client->followRedirect();
    }
}
