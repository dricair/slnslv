<?php
/**
  * Test the Member (User) controller, admin and user functions
  *
  * @author Cédric Airaud
  */

namespace SLN\RegisterBundle\Tests\Controller;

use SLN\RegisterBundle\Tests\Controller\SLNTestCase;

/**
  * Test the Member (User) controller, admin and user functions
  */
class MemberControllerTest extends SLNTestCase
{
    /**
     * Test Member list page
     */
    public function testMemberList() {
        $url = "/admin/member/list";
        $this->assertAdminOnly($url);

        $this->adminLogin();
        $crawler = $this->client->request('GET', $url);
        $this->assertTrue($crawler->filter('h1:contains("Liste des membres")')->count() > 0);

        // @todo check number of members
        // @todo check links
        // @todo check admin image
        
    }


    /**
     * Test inscription page, showing an inline PDF (User and admin)
     */
    public function testInscriptionHtml() {
        // Not the correct user
        $this->userLogin();
        $url = "/member/inscriptions/" . self::TEST_ADMIN_ID;
        $crawler = $this->client->request('GET', $url);
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());

        // Correct user
        $url = "/member/inscriptions/" . self::TEST_USER_ID;
        $crawler = $this->client->request('GET', $url);
        $this->assertTrue($crawler->filter("h1:contains(\"Imprimer les feuilles d'inscription\")")->count() > 0);

        $this->doLogout();

        // Admin access
        $this->adminLogin();
        $crawler = $this->client->request('GET', $url);
        $this->assertTrue($crawler->filter("h1:contains(\"Imprimer les feuilles d'inscription\")")->count() > 0);
    }


    /**
     * Test inscription PDF (User and Admin)
     */
    public function testInscriptionPdf() {
        // Wrong user
        $this->userLogin();
        $url = "/member/inscriptions/" . self::TEST_ADMIN_ID . "/inscriptions.pdf";
        $this->assertAdminOnly($url);

        // Correct user
        // @todo check that PDF is correct
        //$url = "/member/inscriptions/" . self::TEST_USER_ID . "/inscriptions.pdf";
        //$crawler = $this->client->request('GET', $url);
        //ob_start();
        //$this->client->getResponse()->sendContent();
        //$content = ob_get_contents();
        //ob_end_clean();

        // Admin access
        // @todo check that PDF is correct
        //$this->adminLogin();
        //$crawler = $this->client->request('GET', $url);
        //ob_start();
        //$this->client->getResponse()->sendContent();
        //$content = ob_get_contents();
        //ob_end_clean();
    }


    /**
     * Create a member (Admin)
     */
    public function testCreateMember() {
        $url = "/admin/member/create";
        $this->assertAdminOnly($url);
        $this->assertAdminOnly($url, False);

        $this->adminLogin();
        $crawler = $this->client->request('GET', $url);
        $this->assertTrue($crawler->filter("h1:contains('Ajouter un membre')")->count() == 1);
        $this->assertTrue($crawler->filter("div.checkbox:contains('Administrateur')")->count() == 1);
        $this->assertTrue($crawler->filter("html:contains('Droits spéciaux')")->count() == 1);
        
        // @todo Check form
        // @todo Check that user is created
        // @todo Check that fields are correct
        // @todo Check that admin rights are correct
        // @todo Check that mail is sent
        // @todo Check no licensee list
        // @todo Check no add licensee button
    }


    /**
     * Edit a user (Admin)
     */
    public function testEditUser() {
        $url = "/admin/member/edit/" . self::TEST_USER_ID;

        $this->assertAdminOnly($url);
        $this->assertAdminOnly($url, False);

        $this->adminLogin();
        $crawler = $this->client->request('GET', $url);
        $this->assertTrue($crawler->filter("h1:contains('Modifier un membre')")->count() == 1);
        $this->assertTrue($crawler->filter("div.checkbox:contains('Administrateur')")->count() == 1);
        $this->assertTrue($crawler->filter("html:contains('Droits spéciaux')")->count() == 1);

        // @todo Test form
        // @todo Check admin rights
        // @todo Test modified fields
        // @todo Check that mail is not sent.
        // @todo Check licensee list (Empty or not) for admin
        // @todo Check add licensee button
    }


    /**
     * Delete a member (Admin)
     * @todo No delete function yet.
     */
    //public function testDeleteUser() {
    //    $url = "/admin/member/delete/" . self::TEST_USER_ID;
    //    $this->assertAdminOnly($url);

    //    $this->adminLogin();
    //    $crawler = $this->client->request('GET', $url);
    //    
    //    // Check that user does not exist anymore
    //    $repository = $this->getDoctrineManager()->getRepository('SLNRegisterBundle:User');
    //    $this->assertTrue($repository->find(self::TEST_USER_ID) === null);
    //    $crawler = $this->client->request('GET', $url);
    //    $this->assertTrue($this->client->getResponse()->isNotFound());
    //}
    
}

