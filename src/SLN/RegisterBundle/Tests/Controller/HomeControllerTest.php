<?php
/**
  * Test the home controller, mainly the user functions
  *
  * @author Cédric Airaud
  */

namespace SLN\RegisterBundle\Tests\Controller;

use Liip\FunctionalTestBundle\Test\WebTestCase;

/**
  * Test the home controller through web access, mainly the user functions
  *
  * This tests the user functions:
  * - No access when not logged in
  * - Creation of a user
  * - Access to user pages
  */
class HomeControllerTest extends WebTestCase
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
        $this->loadFixtures(array(
            'SLN\RegisterBundle\DataFixtures\ORM\LoadUserData',
        ));

    }

    /**
     * Test user not logged in and wrong login
     */
    public function testIndexLogin()
    {
        $crawler = $this->client->request('GET', '/');
        $this->assertTrue($crawler->filter('html:contains("S\'inscrire au club")')->count() > 0);

        foreach(array("/licensee/create/1",
                      "/contact",
                      "/profile/edit",
                      "/profile/change-password",
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

        $this->assertEquals(preg_match("/http:\/\/.*register\/confirm\/\S+/", $message->getBody(), $matches), 1);
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
     

    /**
     * Test adding a licensee from the home page
     */
    public function testAddLicenseeInline() {
        $this->doLogin(self::TEST_USER, self::TEST_USER_PWD);

        $crawler = $this->client->request('GET', '/');
        $this->assertTrue($crawler->filter('form')->count() == 1);

        /**
         * @todo Fill the form and send
         * @todo Check no field "Admin"
         * @todo Check title contains correct year
         * @todo Check list of licensee in table
         * @todo Check link for inscription sheets
         * @todo Check link for licensee sheet
         * @todo Check Delete and Edit actions
         */
    }


    /**
     * Test User profile edit
     */
    public function testProfileEdit() {
        $this->doLogin(self::TEST_USER, self::TEST_USER_PWD);

        $crawler = $this->client->request('GET', '/profile/edit');
        $this->assertTrue($crawler->filter('html:contains("Modifier les informations de connection")')->count() > 0);
        $this->assertTrue($crawler->filter('html:contains("Modifier l\'identité")')->count() > 0);
        $this->assertTrue($crawler->filter('html:contains("Vérifier le mot de passe")')->count() > 0);

        /**
         * @todo Check change of username and email
         * @todo Check change of address and telephone
         * @todo Check that it does not work if password is not given or wrong
         */
    }

    /**
     * Test User profile edit
     */
    public function testPasswordChange() {
        $this->doLogin(self::TEST_USER, self::TEST_USER_PWD);

        $crawler = $this->client->request('GET', '/profile/change-password');
        $this->assertTrue($crawler->filter('html:contains("Changement de mot de passe")')->count() > 0);

        /**
         * @todo Check password change
         * @todo Check that if password is wrong or not provided it does not work
         * @todo Check that password can be changed twice in a row
         */
    }


    /**
     * Test logout
     */
    public function testLogout() {
        $this->doLogin(self::TEST_USER, self::TEST_USER_PWD);
    
        $crawler = $this->client->request('GET', '/logout');
        $this->assertTrue($this->client->getResponse()->isRedirect());
        $crawler = $this->client->followRedirect();
        $this->assertTrue($crawler->filter('html:contains("S\'inscrire au club")')->count() > 0);
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
