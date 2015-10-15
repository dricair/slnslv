<?php
/**
  * UploadFile class, to permit adding files in a form
  *
  * @author Cédric Airaud
  */

namespace SLN\RegisterBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

use SLN\RegisterBundle\Entity\User;

/**
 * Class for uploaded file, used in LicenseeSelect
 *
 * @ORM\Entity(repositoryClass="SLN\RegisterBundle\Entity\Repository\UploadFileRepository")
 * @ORM\Table(name="uploadfile")
 * @ORM\HasLifecycleCallbacks()
 */
class UploadFile {

    /**
     * @var int $id Id of the Licensee
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string $filename Filename of the uploaded file
     * @ORM\Column(type="string", length=250)
     */
    protected $filename;
    
    /**
     * @var string $path Path of the uploaded file on the disk
     * @ORM\Column(type="string", length=PHP_MAXPATHLEN)
     */
    protected $filepath;
    
    /**
     * @var bool $inline True if the file should be inlined (vs a link)
     * @ORM\Column(type="boolean")
     */
    protected $inline;


    /**
     * @var User $user User that uploaded the file
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(nullable=false)
     */
    protected $user;


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


    /** @ignore */
    public function __construct() {
        $this->setCreated(new \DateTime());
        $this->setUpdated(new \DateTime());
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
     * Set filename
     *
     * @param string $filename
     * @return UploadFile
     */
    public function setFilename($filename)
    {
        $this->filename = $filename;

        return $this;
    }

    /**
     * Get filename
     *
     * @return string 
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * Set filepath
     *
     * @param string $filepath
     * @return UploadFile
     */
    public function setFilepath($filepath)
    {
        $this->filepath = $filepath;

        return $this;
    }

    /**
     * Get filepath
     *
     * @return string 
     */
    public function getFilepath()
    {
        return $this->filepath;
    }

    /**
     * Set inline
     *
     * @param boolean $inline
     * @return UploadFile
     */
    public function setInline($inline)
    {
        $this->inline = $inline;

        return $this;
    }

    /**
     * Get inline
     *
     * @return boolean 
     */
    public function getInline()
    {
        return $this->inline;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     * @return UploadFile
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
     * @return UploadFile
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
     * Set user
     *
     * @param User $user
     * @return UploadFile
     */
    public function setUser(\SLN\RegisterBundle\Entity\User $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \SLN\RegisterBundle\Entity\User 
     */
    public function getUser()
    {
        return $this->user;
    }
}
