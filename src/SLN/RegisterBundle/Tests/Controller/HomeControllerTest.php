<?php
/**
  * Test the home controller, mainly the user functions
  *
  * @author Cédric Airaud
  */

namespace SLN\RegisterBundle\Tests\Controller;

use SLN\RegisterBundle\Tests\Controller\SLNTestCase;

/**
  * Test the home controller through web access, mainly the user functions
  *
  * This tests the user functions:
  * - No access when not logged in
  * - Creation of a user
  * - Access to user pages
  */
class HomeControllerTest extends SLNTestCase
{

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
            $this->assertLoginPage($crawler);
        }

        /**
         * Login test (User does not exist)
         */
        $this->doLogin("test@test.com", "test");
        $this->assertLoginPage($crawler);
    }


    /**
     * Test menu: admin menu should not appear if no admin rights. Check Profile menu.
     */
    public function testMenu() {
        $this->userLogin();
        $crawler = $this->client->request('GET', '/');
        $menu = $crawler->filter(".navbar")->first();

        $user = $this->getDoctrineManager()->getRepository('SLNRegisterBundle:User')->find(self::TEST_USER_ID);
        $nom = $user->getNom();
        $prenom = $user->getPrenom();

        $this->assertTrue($menu->filter('a:contains("Stade Laurentin Natation")')->count() == 1);
        $this->assertTrue($menu->filter('a:contains("Mes licenciés")')->count() == 1);
        $this->assertTrue($menu->filter('a:contains("Administration")')->count() == 0);
        $this->assertTrue($menu->filter('a:contains("Bonjour $prenom $nom")')->count() == 0);

        $this->doLogout();

        $this->adminLogin();
        $crawler = $this->client->request('GET', '/');
        $menu = $crawler->filter("nav.navbar")->first();

        $user = $this->getDoctrineManager()->getRepository('SLNRegisterBundle:User')->find(self::TEST_ADMIN_ID);
        $nom = $user->getNom();
        $prenom = $user->getPrenom();

        $this->assertTrue($menu->filter('a:contains("Stade Laurentin Natation")')->count() == 1);
        $this->assertTrue($menu->filter('a:contains("Mes licenciés")')->count() == 1);
        $this->assertTrue($menu->filter('a:contains("Administration")')->count() == 1);
        $this->assertTrue($menu->filter('a:contains("Bonjour $prenom $nom")')->count() == 0);
    }

    /**
     * Create a user, intercept email, activate, logout and login
     */
    public function testCreateUser() {
        $crawler = $this->client->request('GET', '/register/');
        $this->assertTrue($crawler->filter("div.checkbox:contains('Administrateur')")->count() == 0);
        $this->assertTrue($crawler->filter("html:contains('Droits spéciaux')")->count() == 0);

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
        $this->userLogin();

        // No inline form when licensees exist
        $crawler = $this->client->request('GET', '/');
        $this->assertTrue($crawler->filter('form')->count() == 0);

        // Remove licensees so that inline form appears
        $manager = $this->getDoctrineManager();
        $user = $manager->getRepository('SLNRegisterBundle:User')->find(self::TEST_USER_ID);
        $licensees = $user->getLicensees();
        foreach($licensees as $licensee) {
          $user->removeLicensee($licensee);
          $manager->remove($licensee);
        }

        $manager->persist($user);
        $manager->flush();

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
     * @todo Test inscription list and price
     * @todo Test payment list
     */


    /**
     * Test User profile edit
     */
    public function testProfileEdit() {
        $this->userLogin();

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
        $this->userLogin();

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
        $this->userLogin();
    
        $crawler = $this->client->request('GET', '/logout');
        $this->assertTrue($this->client->getResponse()->isRedirect());
        $crawler = $this->client->followRedirect();
        $this->assertTrue($crawler->filter('html:contains("S\'inscrire au club")')->count() > 0);
    }

}
