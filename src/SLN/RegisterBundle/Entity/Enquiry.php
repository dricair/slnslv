<?php
/**
  * Simple class for information storage of information in the Contact page.
  *
  * @author Cédric Airaud
  */

namespace SLN\RegisterBundle\Entity;

use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;

/**
 * Enquiry class for the Contact page
 */
class Enquiry
{
    /**
     * @var string name Name of the user
     */
    protected $name;

    /**
     * @var string email Email of the user
     */
    protected $email;

    /**
     * @var string subject Subject for the mail
     */
    protected $subject;

    /**
     * @var string body Body for the mail
     */
    protected $body;

    /**
     * Get the name
     *
     * @return string Name
     */
    public function getName() { return $this->name; }

    /**
     * Set the name
     *
     * @param string name Name of the user
     */
    public function setName($name) { $this->name = $name; }

    /**
     * Get the email
     *
     * @return string Email
     */
    public function getEmail() { return $this->email; }

    /**
     * Set the email
     *
     * @param string email Email address
     */
    public function setEmail($email) { $this->email = $email; }

    /**
     * Get the subject
     *
     * @return string Subject for the mail
     */
    public function getSubject() { return $this->subject; }

    /**
     * Set the subject
     *
     * @param string subject Subject for the mail
     */
    public function setSubject($subject) { $this->subject = $subject; }

    /**
     * Get the body for the mail
     *
     * @return string Body for the mail
     */
    public function getBody() { return $this->body; }

    /**
     * Set the body
     *
     * @param string subject Body for the mail
     */
    public function setBody($body) { $this->body = $body; }

    /**
     * Constraints for the forms
     * 
     * @param ClassMetadata $metadata Metadata to modify
     */
    public static function loadValidatorMetadata(ClassMetadata $metadata)
    {
        $metadata->addPropertyConstraint('name', new NotBlank());

        $metadata->addPropertyConstraint('email', new Email());

        $metadata->addPropertyConstraint('subject', new NotBlank());
        $metadata->addPropertyConstraint('subject', new Length(array('max' => 50)));

        $metadata->addPropertyConstraint('body', new Length(array('min' => 3)));
    }
}
