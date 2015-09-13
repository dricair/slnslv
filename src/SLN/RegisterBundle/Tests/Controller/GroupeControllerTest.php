<?php
/**
  * Test the Groupe controller, admin and user functions
  *
  * @author Cédric Airaud
  */

namespace SLN\RegisterBundle\Tests\Controller;

use SLN\RegisterBundle\Tests\Controller\SLNTestCase;

/**
  * Test the Groupe controller, admin and user functions
  */
class GroupeControllerTest extends SLNTestCase
{

    /**
     * Test list of Groupe
     */
    public function testGroupeList() {
        $url = '/admin/groupe/list';
        $this->assertAdminOnly($url);

        $this->adminLogin();
        $crawler = $this->client->request('GET', $url);
        $this->assertTrue($crawler->filter('h1:contains("Liste des groupes de natation")')->count() > 0);
        $this->assertTrue($crawler->filter('h2:contains("Ecole de natation")')->count() > 0);
        $this->assertTrue($crawler->filter('h2:contains("Sections compétition")')->count() > 0);
        $this->assertTrue($crawler->filter('h2:contains("Ados et loisirs")')->count() > 0);

        /**
         * @todo check list
         * @todo check links
         * @todo check number of groupes per list
         */
    }

    /**
     * Test groupe create (Admin only)
     */
    public function testGroupeCreate() {
        $url = '/admin/groupe/create/';
        $this->assertAdminOnly($url);
        $this->assertAdminOnly($url, False);

        $this->adminLogin();
        $crawler = $this->client->request('GET', $url);
        $this->assertTrue($crawler->filter('h1:contains("Ajouter un groupe")')->count() > 0);

        /**
         * @todo check form, check group created
         */
    }

    /**
     * 
     */
    public function testGroupeEdit() {
        $url = '/admin/groupe/edit/1';
        $this->assertAdminOnly($url);
        $this->assertAdminOnly($url, False);

        $this->adminLogin();
        $crawler = $this->client->request('GET', $url);
        $this->assertTrue($crawler->filter('h1:contains("Editer ce groupe")')->count() > 0);
        /**
         * @todo check if user does not exist
         * @todo check modification
         * @todo check multiple
         */
    }

    /**
     * 
     */
    public function testGroupeShow() {
        $url = '/admin/groupe/show/1';
        $this->assertAdminOnly($url);

        $this->adminLogin();
        $crawler = $this->client->request('GET', $url);
        $this->assertTrue($crawler->filter('h1:contains("Groupe de natation: Otaries")')->count() > 0);

        /**
         * @todo check details
         * @todo check links
         */
    }

    /**
     * Test delete of a Groupe
     */
    public function testGroupeDelete() {
        $url = '/admin/groupe/delete/3';
        $this->assertAdminOnly($url);

        // Check delete
        $this->adminLogin();
        $crawler = $this->client->request('GET', $url);
        $this->assertTrue($this->client->getResponse()->isRedirect());
        $crawler = $this->client->followRedirect();
        $this->assertTrue($crawler->filter('h1:contains("Liste des groupes de natation")')->count() > 0);

        $repository = $this->getDoctrineManager()->getRepository('SLNRegisterBundle:Groupe');
        $this->assertTrue($repository->find(3) === null);

        $crawler = $this->client->request('GET', $url);
        $this->assertTrue($this->client->getResponse()->isNotFound());
        
        /**
         * @todo check not delete if users in group
         */
    }
}

