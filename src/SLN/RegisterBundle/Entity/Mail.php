<?php
/**
 * Class to send formatted mail to various people
 */

namespace SLN\RegisterBundle\Entity;

use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;

use SLN\RegisterBundle\Entity\Licensee;
use SLN\RegisterBundle\Entity\Groupe;

/**
 * Mail class
 */
class FormatedMail {

    /**
     * @var string $title Title of the mail
     */
    public $title;

    /**
     * @var array[Licensee] $licensees List of licensees to send the mail to
     */
    public $licensees;

    /**
     * @var array[Groupe] $groupes List of groups to send the mail to
     */
    public $groupes;

    /**
     * @var string $body Text of the mail, can be formated
     */
    public $body;

    /** 
     * @var string $sender Sender of the mail
     */
    public $sender;
}

