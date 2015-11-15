<?php
/**
  * Mail sent to one of more licensees
  *
  * @author Cédric Airaud
  */

namespace SLN\RegisterBundle\Entity;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

use SLN\RegisterBundle\Entity\Licensee;
use SLN\RegisterBundle\Entity\UploadFile;
use SLN\RegisterBundle\Entity\Groupe;

require_once("Html2Text.php");

/**
 * LicenseeMail class, representing a mail to one or more licensees
 *
 * @ORM\Entity(repositoryClass="SLN\RegisterBundle\Entity\Repository\LicenseeMailRepository")
 * @ORM\Table(name="licensee_mail")
 * @ORM\HasLifecycleCallbacks()
 */
class LicenseeMail {

    /**
     * @var int $id Id of the Licensee
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string $title Title of the mail
     * @ORM\Column(type="string", length=250)
     *
     * * @Assert\NotEqualTo(
     *     value = LicenseeMail::DEFAULT_TITLE,
     *     message = "Vous n'avez pas changé le titre du message"
     * )
     */
    protected $title;

    const DEFAULT_TITLE = "Titre du mail";

    /**
     * @var string $body Body of the mail 
     * @ORM\Column(type="text")
     */
    protected $body;

    const DEFAULT_BODY = "Cliquez directement sur le titre et le texte pour éditer le message à envoyer";

    /** 
     * Validate the body part in a callback function
     * @Assert\Callback
     */
    public function validate(ExecutionContextInterface $context)
    {
        // Vérifie si le nom est bidon
        if (strpos($this->body, self::DEFAULT_BODY) !== false) {
           $context->addViolationAt(
                'body',
                "Vous n'avez pas modifié le texte du message",
                array(),
                null
            );
        }
    }

    /**
     * @var Licensee[] $licensees List of licensees to send the mail to
     *
     * @ORM\ManyToMany(targetEntity="SLN\RegisterBundle\Entity\Licensee", cascade={"persist"})
     */
    protected $licensees;


    /**
     * @var User $sender Licensee sending the mail
     *
     * @ORM\ManyToOne(targetEntity="SLN\RegisterBundle\Entity\User")
     * @ORM\JoinColumn(nullable=false)
     */
    protected $sender;


    /**
     * @var UploadFile[] $files List of attached files
     *
     * @ORM\ManyToMany(targetEntity="SLN\RegisterBundle\Entity\UploadFile", cascade={"persist"})
     */
    protected $files;


    /**
     * @var \Datetime $created Date of creation
     * @ORM\Column(type="datetime")
     */
    protected $created;


    /**
     * @var \Datetime $updated Date of last update
     * @ORM\Column(type="datetime")
     */
    protected $updated;


    /**
     * @var Groupe $groupe Temporary group for the form
     */
    public $groupe;


    /**
     * Return body as a text
     *
     * @return string
     */
    public function getBodyAsText() {
        $ht = new \Html2Text\Html2Text($this->body);
        return $ht->getText();
    }


    /**
     * Return title as a text
     *
     * @return string
     */
    public function getTitleAsText() {
        $ht = new \Html2Text\Html2Text($this->title);
        return $ht->getText();
    }


    /** @ignore */
    public function __construct() {
        $this->setCreated(new \DateTime());
        $this->setUpdated(new \DateTime());
        $this->licensees = new ArrayCollection();
        $this->files = new ArrayCollection();
        $this->title = self::DEFAULT_TITLE;
        $this->body = self::DEFAULT_BODY;
    }


    /**
     * @ignore
     * @ORM\PreUpdate
     */
    public function setUpdatedValue()
    {
       $this->setUpdated(new \DateTime());
    }



    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set title
     *
     * @param string $title
     * @return LicenseeMail
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string 
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set body
     *
     * @param string $body
     * @return LicenseeMail
     */
    public function setBody($body)
    {
        $this->body = $body;

        return $this;
    }

    /**
     * Get body
     *
     * @return string 
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     * @return LicenseeMail
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created
     *
     * @return \DateTime 
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set updated
     *
     * @param \DateTime $updated
     * @return LicenseeMail
     */
    public function setUpdated($updated)
    {
        $this->updated = $updated;

        return $this;
    }

    /**
     * Get updated
     *
     * @return \DateTime 
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    /**
     * Add licensee
     *
     * @param \SLN\RegisterBundle\Entity\Licensee $licensee
     * @return LicenseeMail
     */
    public function addLicensee(Licensee $licensee)
    {
        $this->licensees[] = $licensee;

        return $this;
    }

    /**
     * Remove licensee
     *
     * @param \SLN\RegisterBundle\Entity\Licensee $licensee
     */
    public function removeLicensee(Licensee $licensee)
    {
        $this->licensees->removeElement($licensee);
    }

    /**
     * Get licensees
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getLicensees()
    {
        return $this->licensees;
    }

    /**
     * Set sender
     *
     * @param User $sender
     * @return LicenseeMail
     */
    public function setSender(User $sender)
    {
        $this->sender = $sender;

        return $this;
    }

    /**
     * Get sender
     *
     * @return User 
     */
    public function getSender()
    {
        return $this->sender;
    }

    /**
     * Add files
     *
     * @param UploadFile $files
     * @return LicenseeMail
     */
    public function addFile(UploadFile $files)
    {
        $this->files[] = $files;

        return $this;
    }

    /**
     * Remove files
     *
     * @param UploadFile $files
     */
    public function removeFile(UploadFile $files)
    {
        $this->files->removeElement($files);
    }

    /**
     * Get files
     *
     * @return ArrayCollection 
     */
    public function getFiles()
    {
        return $this->files;
    }


    /**
     * Set files
     *
     * @param UploadFile[] $files 
     * @return LicenseeMail
     */
    public function setFiles($files)
    {
        $this->files = new ArrayCollection();
        foreach ($files as $file)
          $this->files[] = $file;

        return $this;
    }
}
