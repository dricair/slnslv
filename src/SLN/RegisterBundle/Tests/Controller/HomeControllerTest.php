<?php

namespace SLN\RegisterBundle\Tests\Controller;

use Liip\FunctionalTestBundle\Test\WebTestCase;

class HomeControllerTest extends WebTestCase
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

    /**
     * Test user not logged in and wrong login
     */
    public function testIndexLogin()
    {
        $this->loadFixtures(array(
            'SLN\RegisterBundle\DataFixtures\ORM\LoadUserData',
        ));

        $crawler = $this->client->request('GET', '/');
        $this->assertTrue($crawler->filter('html:contains("S\'inscrire au club")')->count() > 0);

        foreach(array("/licensee/create/1",
                      "/contact",
                      "/profile/edit",
                      "/admin/member/list") as $url) {
            $crawler = $this->client->request('GET', $url);
            $this->assertTrue($this->client->getResponse()->isRedirect());
            $crawler = $this->client->followRedirect();
            $this->assertTrue($crawler->filter('html:contains("Se connecter à un compte utilisateur")')->count() > 0);
        }

        /**
         * Login test (User does not exist
         */
        $this->doLogin("test@test.com", "test");
        $this->assertTrue($crawler->filter('html:contains("Se connecter à un compte utilisateur")')->count() > 0);
    }

    /**
     * Create a user, intercept email, activate, logout and login
     */
    public function testCreateUser() {
        $crawler = $this->client->request('GET', '/register/');
        $form = $crawler->selectButton('_submit')->form(array(
          'fos_user_registration_form[nom]'          => "Test",
          'fos_user_registration_form[prenom]'       => "Test-Prenom",
          'fos_user_registration_form[adresse]'      => "Adresse",
          'fos_user_registration_form[code_postal]'  => "06700",
          'fos_user_registration_form[ville]'        => "St Laurent du Var",
          'fos_user_registration_form[tel_portable]' => "0606515260",
          'fos_user_registration_form[email]'        => "test@test.com",
          'fos_user_registration_form[username]'     => "test",
          'fos_user_registration_form[plainPassword][first]'  => "test",
          'fos_user_registration_form[plainPassword][second]' => "test",
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

        // Asserting email data
        $this->assertInstanceOf('Swift_Message', $message);
        $this->assertEquals('Bienvenue test !', $message->getSubject());
        $this->assertEquals('slnslv@free.fr', key($message->getFrom()));
        $this->assertEquals("cairaud@gmail.com", key($message->getTo()));
        $this->assertContains(
            'Pour valider votre compte utilisateur, ',
            $message->getBody()
        );

        $this->assertEquals(preg_match("/http:\/\/.*register\/confirm\/\w+/", $message->getBody(), $matches), 1);
        $this->assertEquals(count($matches), 1);
        $confirmUrl = $matches[0];

        $crawler = $this->client->followRedirect();
        $this->assertTrue($crawler->filter('html:contains("L\'utilisateur a été créé avec succès")')->count() > 0);

        // Go to confirmation address
        $this->client->request('GET', $confirmUrl);
        $this->assertTrue($this->client->getResponse()->isRedirect());
        $crawler = $this->client->followRedirect();
        $this->assertTrue($crawler->filter('html:contains("Félicitation test, votre compte est maintenant activé.")')->count() > 0);
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
