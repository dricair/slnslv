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
        $url = "/admin/licensee/list";
        $this->assertAdminOnly($url);
        
        $this->adminLogin();
        $crawler = $this->client->request('GET', $url);

        $this->assertTrue($crawler->filter('h1:contains("Liste des licenciés")')->count() > 0);
        //$this->assertTrue($crawler->filter('h2:contains("Pas de groupe sélectionné")')->count() > 0);
        $this->assertTrue($crawler->filter('h2:contains("Otaries")')->count() > 0);
        $this->assertTrue($crawler->filter('h2:contains("Compétition")')->count() > 0);
        $this->assertTrue($crawler->filter('h2:contains("Loisirs")')->count() > 0);
        $this->assertTrue($crawler->filter('h2:contains("Loisirs")')->count() > 0);
        //$this->assertTrue($crawler->filter('h1:contains("Fonctions spéciales")')->count() > 0); @todo this should match
        $this->assertTrue($crawler->filter('h2:contains("Officiel")')->count() > 0);
        $this->assertTrue($crawler->filter('h2:contains("Membre du bureau")')->count() > 0);
        $this->assertTrue($crawler->filter('h2:contains("Entraineur")')->count() > 0);
        
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
        $this->assertTrue($crawler->filter('h1:contains("Ajouter un licencié")')->count() > 0);
        $this->assertTrue($crawler->filter('html:contains("Compte de rattachement")')->count() == 0);
        
        // @todo check form
        // @todo check licensee is created
        // @todo check licensee is created on correct user

        $this->doLogout();

        $url = '/admin/licensee/create';
        $this->assertAdminOnly($url);
        $this->assertAdminOnly($url, False);

        // Admin page: create a licensee
        $this->adminLogin();
        $crawler = $this->client->request('GET', $url);
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
        $this->assertTrue($crawler->filter('h1:contains(\'Editer le licencié "Prenom1 Nom1"\')')->count() > 0);
        $this->assertTrue($crawler->filter('html:contains("Compte de rattachement")')->count() == 0);

        // @todo check form
        // @todo check new values

        $this->doLogout();

        $url = '/admin/licensee/edit/2';
        $this->assertAdminOnly($url);
        $this->assertAdminOnly($url, False);

        // Admin page: edit a licensee
        $this->adminLogin();
        $crawler = $this->client->request('GET', $url);
        $this->assertTrue($crawler->filter('h1:contains(\'Editer le licencié "Prenom2 Nom2"\')')->count() > 0);
        $this->assertTrue($crawler->filter('html:contains("Compte de rattachement")')->count() > 0);

        // @todo check form
        // @todo check new values
        // @todo check on correct user
    }


    /**
     * Test licensee show
     */
    public function testLicenseeShow() {
        // @todo /licensee/{id}
    }


    /**
     * Test inscription sheet (PDF) for a licensee
     */
    public function testLicenseeInscription() {
        $url = "/admin/licensee/inscription/1";
        $this->assertAdminOnly($url);
         
        $this->adminLogin();

        // Load file into a output buffer
        // @todo check valid PDF ?
        //$this->client->request('GET', $url);
        //ob_start();
        //$this->client->getResponse()->sendContent();
        //$content = ob_get_contents();
        //ob_end_clean();
    }


    /**
     * Test delete of a licensee (User and Admin)
     */
    public function testLicenseeDelete() {
        // Not the correct user
        $this->userLogin();
        $url = '/licensee/delete/4';
        $crawler = $this->client->request('GET', $url);
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());

        // Correct user
        $url = '/licensee/delete/1';
        $crawler = $this->client->request('GET', $url);
        $this->assertTrue($this->client->getResponse()->isRedirect());
        $crawler = $this->client->followRedirect();
        $homepage = $this->client->getContainer()->get('router')->generate('SLNRegisterBundle_homepage', array(), True);
        $this->assertTrue($this->client->getRequest()->getUri() == $homepage);

        // Check that licensee does not exist anymore
        $repository = $this->getDoctrineManager()->getRepository('SLNRegisterBundle:Licensee');
        $this->assertTrue($repository->find(1) === null);
        $crawler = $this->client->request('GET', $url);
        $this->assertTrue($this->client->getResponse()->isNotFound());

        $this->doLogout();

        // Admin delete
        $url = '/licensee/delete/4';
        $this->assertAdminOnly($url);

        $url = '/licensee/delete/2';
        $this->adminLogin();
        $crawler = $this->client->request('GET', $url);
        $this->assertTrue($this->client->getResponse()->isRedirect());
        $crawler = $this->client->followRedirect();
        $this->assertTrue($this->client->getRequest()->getUri() == $homepage);

        // Check that licensee does not exist anymore
        $repository = $this->getDoctrineManager()->getRepository('SLNRegisterBundle:Licensee');
        $this->assertTrue($repository->find(2) === null);
        $crawler = $this->client->request('GET', $url);
        $this->assertTrue($this->client->getResponse()->isNotFound());
    }


}


