<?php
/**
  * Overrides the WebTestBase class to add useful functions
  *
  * @author Cédric Airaud
  */

namespace SLN\RegisterBundle\Tests\Controller;

use SLN\RegisterBundle\Tests\Controller\HTMLValidate;
use Liip\FunctionalTestBundle\Test\WebTestCase;

class SLNTestCase extends WebTestCase {

    /** Normal user to test */
    const TEST_USER      = "test-user@test.com";
    /** Password for normal user */
    const TEST_USER_PWD  = "test";
    /** Id for normal user */
    const TEST_USER_ID = 2;

    /** User with admin rights */
    const TEST_ADMIN     = "test-admin@test.com";
    /** Password for user with admin */
    const TEST_ADMIN_PWD = "test";
    /** Id for normal user */
    const TEST_ADMIN_ID = 1;

    /** Previous saison */
    const TEST_SAISON_PREV = 1;
    /** Current saison */
    const TEST_SAISON_CURRENT = 2;
    /** Next saison */
    const TEST_SAISON_NEXT = 3;

    /**
     * Set-up: enable profiler
     */
    public function setUp() {
        parent::setUp();
        $this->client = static::createClient();
        $this->client->enableProfiler();

        $this->loadFixtures(array(
            'SLN\RegisterBundle\DataFixtures\ORM\LoadUserData',
            'SLN\RegisterBundle\DataFixtures\ORM\LoadGroupeData',
            'SLN\RegisterBundle\DataFixtures\ORM\LoadSaisonData',
            'SLN\RegisterBundle\DataFixtures\ORM\LoadLicenseeData',
        ));
    }


    /**
     * Return Doctrine Manager
     *
     * @return \Doctrine\ORM\EntityManager Doctrine Manager
     */
    public function getDoctrineManager() {
        return $this->client->getContainer()
               ->get('doctrine')
               ->getManager();
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


    /**
     * Logout
     */
    public function doLogout() {
        $crawler = $this->client->request('GET', '/logout');
    }


    /**
     * Log a normal user, without admin rights
     */
    public function userLogin() {
        $this->doLogin(self::TEST_USER, self::TEST_USER_PWD);
    }

    /**
     * Log a user with admin rights
     */
    public function adminLogin() {
        $this->doLogin(self::TEST_ADMIN, self::TEST_ADMIN_PWD);
    }


    /**
     * Check that current page is login page. Needs to be called after URL Get @todo does not work: but before redirect
     *
     * @param \Symfony\Component\DomCrawler\Crawler $crawler Crawler class, result of call
     */
    public function assertLoginPage($crawler) {
        if ($this->client->getResponse()->isRedirect())
          $crawler = $this->client->followRedirect();
        $this->assertTrue($crawler->filter('h1:contains("Se connecter à un compte utilisateur")')->count() > 0);
    }


    /**
     * Check that specified page is accessible in Admin only
     *
     * Log as user and check the answer is access denied. Logout afterwards.
     *
     * @param string $url    Url to access as User
     * @param bool   $is_get If True access with GET, POST if False. 
     */
     public function assertAdminOnly($url, $is_get=True) {
        $this->userLogin();
        $crawler = $this->client->request($is_get ? 'GET' : 'POST', $url);
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
        $this->doLogout();
     }


     /**
      * Check that the page is HTML5 compliant
      */
     public function verifyHTML5($filename, $client) {
         $validator = new HTMLValidate();
         $content = $client->getResponse()->getContent();
         $result = $validator->Assert($content);

         $post_waivers = array();
         if (!$result) {
             $waivers = array(// icon on li for menu
                              "/Attribute \“icon\” not allowed on element \“li\” at this point./",
                              // Lang=en
                              "/This document appears to be written in English. Consider adding.*lang/");

             $result = true;
             foreach (explode("\n", trim($validator->message)) as $line) {
                 $waived = False;
                 foreach ($waivers as $pattern) {
                     if (preg_match($pattern, $line) == 1) {
                         $waived = True;
                         break;
                     }
                 }
                 if (!$waived and trim($line) != "") {
                     $post_waivers[] = $line;
                     $result = false;
                 }
             }
         }

         $filename = "output/$filename.html";
         if (!$result) {
             file_put_contents($filename, $content);
         }

         $this->assertTrue($result, "See file $filename: \n\n" . implode("\n", $post_waivers));
     }
}


