<?php
/**
  * Test the Misc controller, mainly the user functions
  *
  * @author Cédric Airaud
  */

namespace SLN\RegisterBundle\Tests\Controller;

use SLN\RegisterBundle\Tests\Controller\SLNTestCase;

/**
  * Test the Misc controller, mainly the user functions
  */
class MiscControllerTest extends SLNTestCase
{
    /**
     * Test the contact view.
     *
     * Test that:
     * - Form can be submitted
     * - Mail is sent with the correct content
     */
    public function testContact() {

        $this->userLogin();

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
}
