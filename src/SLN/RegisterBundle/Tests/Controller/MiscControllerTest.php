<?php

namespace SLN\RegisterBundle\Tests\Controller;

use Liip\FunctionalTestBundle\Test\WebTestCase;

class MiscControllerTest extends WebTestCase
{
    const TEST_USER      = "test-user@test.com";
    const TEST_USER_PWD  = "test";
    const TEST_ADMIN     = "test-admin@test.com";
    const TEST_ADMIN_PWD = "test";

    public function setUp() {
        parent::setUp();
        $this->client = static::createClient();
        $this->client->enableProfiler();
    }

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
