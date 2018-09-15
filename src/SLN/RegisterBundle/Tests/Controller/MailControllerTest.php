<?php
/**
  * Test the Mail controller
  *
  * @author Cédric Airaud
  */

namespace SLN\RegisterBundle\Tests\Controller;

use SLN\RegisterBundle\Tests\Controller\SLNTestCase;

/**
  * Test the Mail
  */
class MailControllerTest extends SLNTestCase
{
    public function testMail() {
        $em = $this->getDoctrineManager();
        $url = "/admin/mail/" . self::TEST_SAISON_CURRENT . "/licensee";
        $this->assertAdminOnly($url);
        
        $this->adminLogin();
        $crawler = $this->client->request('GET', $url);
        $this->verifyHTML5("mail_edit", $this->client);
        $this->assertTrue($crawler->filter('h1:contains("Envoi de mails")')->count() > 0);

        // @todo Edit and send mail
        // @todo Back from confirm to edit page
        // @todo Default licencee
        // @todo Default Groupe
        // @todo Attach file
    }
}


