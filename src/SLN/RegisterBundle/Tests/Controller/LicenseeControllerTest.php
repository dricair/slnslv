<?php
/**
  * Test the Licensee controller, admin and user functions
  *
  * @author Cédric Airaud
  */

namespace SLN\RegisterBundle\Tests\Controller;

use SLN\RegisterBundle\Tests\Controller\SLNTestCase;

/**
  * Test the Licensee controller, admin and user functions
  */
class LicenseeControllerTest extends SLNTestCase
{
    /**
     * Check list of licensees
     */
    public function testLicenseeList() {
        $em = $this->getDoctrineManager();
        $saison = $em->getRepository('SLNRegisterBundle:Saison')->find(self::TEST_SAISON_CURRENT);
        $url = "/admin/licensee/" . $saison->getId() . "/list";
        $this->assertAdminOnly($url);
        
        $this->adminLogin();
        $crawler = $this->client->request('GET', $url);
        $this->verifyHTML5("licensee_list", $this->client);

        $title = "Liste des licenciés pour la saison " . $saison->getNom();
        $this->assertTrue($crawler->filter("h1:contains($title)")->count() > 0);
        //$this->assertTrue($crawler->filter('h2:contains("Pas de groupe sélectionné")')->count() > 0);
        $this->assertTrue($crawler->filter('h2:contains("Otaries")')->count() > 0);
        $this->assertTrue($crawler->filter('h2:contains("Compétition")')->count() > 0);
        $this->assertTrue($crawler->filter('h2:contains("Loisirs")')->count() > 0);
        $this->assertTrue($crawler->filter('h2:contains("Loisirs")')->count() > 0);
        //$this->assertTrue($crawler->filter('h1:contains("Fonctions spéciales")')->count() > 0); @todo this should match
        $this->assertTrue($crawler->filter('h2:contains("Officiel")')->count() > 0);
        $this->assertTrue($crawler->filter('h2:contains("Membre du bureau")')->count() > 0);
        $this->assertTrue($crawler->filter('h2:contains("Entraineur")')->count() > 0);

        // @todo Register again through list
        // @todo Verify number of entries 
    }

    /**
     * Test Licensee create (User and Admin)
     */
    public function testLicenseeCreate() {
        // Not the correct user
        $this->userLogin();
        $url = '/licensee/create/' . self::TEST_ADMIN_ID;
        $crawler = $this->client->request('GET', $url);
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());

        // Create with correct user
        $url = '/licensee/create/' . self::TEST_USER_ID;
        $crawler = $this->client->request('GET', $url);
        $this->verifyHTML5("licensee_create_user", $this->client);
        $this->assertTrue($crawler->filter('h1:contains("Ajouter un licencié")')->count() > 0);
        $this->assertTrue($crawler->filter('html:contains("Compte de rattachement")')->count() == 0);
        
        // @todo check form
        // @todo check licensee is created
        // @todo check licensee is created on correct user

        $this->doLogout();

        $url = "/admin/licensee/" . self::TEST_SAISON_CURRENT . "/create";
        $this->assertAdminOnly($url); // Get
        $this->assertAdminOnly($url, False); // Post

        // Admin page: create a licensee
        $this->adminLogin();
        $crawler = $this->client->request('GET', $url);
        $this->verifyHTML5("licensee_create_admin", $this->client);
        $this->assertTrue($crawler->filter('h1:contains("Ajouter un licencié")')->count() > 0);
        $this->assertTrue($crawler->filter('html:contains("Compte de rattachement")')->count() > 0);

        // @todo check form
        // @todo check licensee is created
        // @todo check licensee is created on correct user
        // @todo check selection of multiple depending on group
    }


    /**
     * Test edit of a licensee (User and Admin)
     */
    public function testLicenseeEdit() {
        // Edit not from correct user
        $this->userLogin();
        $url = '/licensee/edit/4';
        $crawler = $this->client->request('GET', $url);
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());

        // Edit from correct user
        $url = '/licensee/edit/1';
        $crawler = $this->client->request('GET', $url);
        $this->verifyHTML5("licensee_edit_user", $this->client);
        $this->assertTrue($crawler->filter('h1:contains(\'Editer le licencié "Prenom1 Nom1"\')')->count() > 0);
        $this->assertTrue($crawler->filter('html:contains("Compte de rattachement")')->count() == 0);

        // @todo check form
        // @todo check new values

        $this->doLogout();

        $url = '/admin/licensee/' . self::TEST_SAISON_CURRENT . '/edit/2';
        $this->assertAdminOnly($url);
        $this->assertAdminOnly($url, False);

        // Admin page: edit a licensee
        $this->adminLogin();
        $crawler = $this->client->request('GET', $url);
        $this->verifyHTML5("licensee_edit_admin", $this->client);
        $this->assertTrue($crawler->filter('h1:contains(\'Editer le licencié "Prenom2 Nom2"\')')->count() > 0);
        $this->assertTrue($crawler->filter('html:contains("Compte de rattachement")')->count() > 0);

        // @todo check form
        // @todo check new values
        // @todo check on correct user
        // @todo Register again through form
    }


    /**
     * Test licensee show
     */
    //public function testLicenseeShow() {
        // @todo /licensee/{id}
    //}


    /**
     * Test inscription sheet (PDF) for a licensee
     */
    public function testLicenseeInscription() {
        // Licensee attached to standard user
        $this->userLogin();
        $url = "/licensee/" . self::TEST_SAISON_CURRENT . "/inscription/1";
        ob_start();
        $crawler = $this->client->request('GET', $url);
        $content = ob_get_contents();
        ob_end_clean();
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertRegexp('/^%PDF-1\.7/', $content);

        // Licensee attached to admin user
        $url = "/licensee/" . self::TEST_SAISON_CURRENT . "/inscription/4";
        ob_start();
        $crawler = $this->client->request('GET', $url);
        ob_end_clean();
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());

        // Licensee attached to standard user from admin
        $this->adminLogin();
        $url = "/licensee/" . self::TEST_SAISON_CURRENT . "/inscription/1";
        ob_start();
        $crawler = $this->client->request('GET', $url);
        $content = ob_get_contents();
        ob_end_clean();
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertRegexp('/^%PDF-1\.7/', $content);
    }


    /**
     * Test delete of a licensee (User)
     */
    public function testLicenseeUserDelete() {
        // Not the correct user
        $this->userLogin();
        $url = '/licensee/delete/4';
        $crawler = $this->client->request('GET', $url);
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());

        // Correct user
        $crawler = $this->client->request('GET', '/'); // Will return to previous page

        $url = '/licensee/delete/3';
        $crawler = $this->client->request('GET', $url);
        $this->assertTrue($this->client->getResponse()->isRedirect());
        $crawler = $this->client->followRedirect();
        $homepage = $this->client->getContainer()->get('router')->generate('SLNRegisterBundle_homepage', array(), True);
        $this->assertTrue($this->client->getRequest()->getUri() == $homepage);

        // Check that licensee does not exist anymore for open saison (saison-next)
        $em = $this->getDoctrineManager();
        $licensee = $em->getRepository('SLNRegisterBundle:Licensee')->find(3);
        $saison   = $em->getRepository('SLNRegisterBundle:Saison')->find(self::TEST_SAISON_NEXT);
        $this->assertTrue($licensee->getSaisonLink($saison) === null);
        $crawler = $this->client->request('GET', $url);
        $this->assertTrue($this->client->getResponse()->isNotFound());
    }

    /**
     * Test delete of a licensee (Admin)
     */
    public function testLicenseeAdminDelete() {
        // Admin delete
        $crawler = $this->client->request('GET', '/admin/licensee/' . self::TEST_SAISON_NEXT . '/list'); // Will return to previous page

        $url = '/licensee/delete/3';
        $this->adminLogin();
        $crawler = $this->client->request('GET', $url);
        $this->assertTrue($this->client->getResponse()->isRedirect());
        $crawler = $this->client->followRedirect();
        $licensee_list = $this->client->getContainer()->get('router')->generate('SLNRegisterBundle_admin_licensee_list', 
                                                                                array('saison_id' => self::TEST_SAISON_NEXT), True);
        $this->assertTrue($this->client->getRequest()->getUri() == $licensee_list);

        // Check that licensee does not exist anymore
        $em = $this->getDoctrineManager();
        $licensee = $em->getRepository('SLNRegisterBundle:Licensee')->find(3);
        $saison   = $em->getRepository('SLNRegisterBundle:Saison')->find(self::TEST_SAISON_NEXT);
        $this->assertTrue($licensee->getSaisonLink($saison) === null);
        $crawler = $this->client->request('GET', $url);
        $this->assertTrue($this->client->getResponse()->isNotFound());
    }


}


