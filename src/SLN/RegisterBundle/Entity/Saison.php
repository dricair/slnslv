<?php
/**
  * Represents a saison, against which licencees can be activated.
  *
  * @author Cédric Airaud
  */

namespace SLN\RegisterBundle\Entity;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\ExclusionPolicy,
    JMS\Serializer\Annotation\Expose,
    JMS\Serializer\Annotation\Groups,
    JMS\Serializer\Annotation\VirtualProperty;

use SLN\RegisterBundle\Entity\Licensee;
use SLN\RegisterBundle\Entity\LicenseeSaison;
use SLN\RegisterBundle\Entity\UserPayment;


// Compare reduction types, by start date
function cmpReduction($a, $b) {
    return $a['start'] - $b['start'];
};


class Reduction {
    
    /**
     * @var \Datetime $start Start month for reduction
     */
    public $start;
    
    /**
     * @var int $reduction Reduction in %
     */
    public $reduction;

    /**
     * Constructor of the class
     *
     * @param timestamp  $start       Start date
     * @param timestamp  $fin         End time
     * @param string     $description Description for the slot
     */
    public function __construct($start=0, $reduction=0) {
        $this->start = $start;
        $this->reduction = $reduction;
    }

    public function getStart() {
        return date('d/m/Y', $this->start);
    }
}

/**
 * Saison class
 *
 * @ORM\Entity(repositoryClass="SLN\RegisterBundle\Entity\Repository\SaisonRepository")
 * @ORM\Table(name="saison")
 * @ORM\HasLifecycleCallbacks()
 * @ExclusionPolicy("all")
 */
class Saison {
    /**
     * @var int $id Id of the saison
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Expose
     */
    protected $id;

    /**
     * @var string $nom Name of the saison
     * @ORM\Column(type="string", length=100)
     *
     * @Assert\NotBlank(message="Merci d'entrer un nom.", groups={"Registration", "Profile"})
     * @Assert\Length(
     *     min=3,
     *     max="100",
     *     minMessage="Le nom est trop court.",
     *     maxMessage="Le nom est trop long.",
     *     groups={"Registration", "Profile"}
     * )
     * @Expose
     */
    protected $nom;

    /**
     * @var \Datetime $start Start date of the saison
     * @ORM\Column(type="datetime")
     * @Expose
     */
    protected $start;


    /**
     * @var bool $nom Activate or not
     * @ORM\Column(type="boolean")
     * @Expose
     */
    protected $activated;

    /**
     * @var Reduction[] $reductions List of reductions depending on date
     * @ORM\Column(type="json_array", nullable=True)
     */
    protected $reductions;

    /**
     * @var LicenseeSaison[] $licensee_links List of licensees links for this saison
     * @ORM\OneToMany(targetEntity="LicenseeSaison", mappedBy="saison")
     */
    protected $licensee_links;

    /**
     * @var UserPayment[] $payments List of payments for this saison
     * @ORM\OneToMany(targetEntity="UserPayment", mappedBy="saison")
     */
    protected $payments;

    /*
     * @var \Datetime $created Creation date
     * @ORM\Column(type="datetime")
     */
    protected $created;

    /**
     * @var \Datetime $updated Last update date
     * @ORM\Column(type="datetime")
     */
    protected $updated;

    /** @ignore */
    public function __construct() {
        $this->setCreated(new \DateTime());
        $this->setUpdated(new \DateTime());

        $this->reductions = Array((array)new Reduction());
        $this->activated = false;
        $this->licensee_links = new ArrayCollection();
        $this->payments = new ArrayCollection();
    }


    /**
     * Set created
     *
     * @param \DateTime $created
     * @return Licensee
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
     * Set nom
     *
     * @param string $nom
     * @return Saison
     */
    public function setNom($nom)
    {
        $this->nom = $nom;

        return $this;
    }

    /**
     * Get nom
     *
     * @return string 
     */
    public function getNom()
    {
        return $this->nom;
    }

    /**
     * Set activated
     *
     * @param boolean $activated
     * @return Saison
     */
    public function setActivated($activated)
    {
        $this->activated = $activated;

        return $this;
    }

    /**
     * Get activated
     *
     * @return boolean 
     */
    public function getActivated()
    {
        return $this->activated;
    }

    /**
     * Add a new Reduction
     * @param Reduction $reduction
     */
    public function addReduction($reduction) {
        $this->reductions[] = $reduction;
    }

    /** 
      * Remove an reduction
      * @param Reduction $reduction
      */
    public function removeReduction($reduction) {
        $index = -1;
        foreach ($this->reductions as $i => $r) {
            if ($r['start'] == $reduction['start'] and
                $r['reduction'] == $reduction['reduction'])
                $index = $i;
        };

        if ($index != -1)
            unset($this->reductions[$index]);
    }
 
    /**
     * Set reductions
     *
     * @param array $reductions
     * @return Saison
     */
    public function setReductions($reductions)
    {
        $this->reductions = $reductions;

        return $this;
    }

    /**
     * Get reductions
     *
     * @return array 
     */
    public function getReductions()
    {
        return $this->reductions;
    }

    /**
     * Get reductions as Reduction list
     *
     * @return Reduction[]
     */
    public function getFormatedReductions() {
        $ret = array();

        usort($this->reductions, "SLN\RegisterBundle\Entity\cmpReduction");
        foreach ($this->reductions as $reduction) {
            $ret[] = new Reduction($reduction['start'], $reduction['reduction']);
        }
        return $ret;
    }

    /**
     * Set updated
     *
     * @param \DateTime $updated
     * @return Saison
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
     * Set start
     *
     * @param \DateTime $start
     * @return Saison
     */
    public function setStart($start)
    {
        $this->start = $start;

        return $this;
    }

    /**
     * Get start
     *
     * @return \DateTime 
     */
    public function getStart()
    {
        return $this->start;
    }

    /**
     * Add licensee_links
     *
     * @param LicenseeSaison $licenseeLinks
     * @return Saison
     */
    public function addLicenseeLink(LicenseeSaison $licenseeLinks)
    {
        $this->licensee_links[] = $licenseeLinks;

        return $this;
    }

    /**
     * Remove licensee_links
     *
     * @param LicenseeSaison $licenseeLinks
     */
    public function removeLicenseeLink(LicenseeSaison $licenseeLinks)
    {
        $this->licensee_links->removeElement($licenseeLinks);
    }

    /**
     * Get licensee_links
     *
     * @return Collection 
     */
    public function getLicenseeLinks()
    {
        return $this->licensee_links;
    }

    /**
     * Add payments
     *
     * @param \SLN\RegisterBundle\Entity\UserPayment $payments
     * @return Saison
     */
    public function addPayment(\SLN\RegisterBundle\Entity\UserPayment $payments)
    {
        $this->payments[] = $payments;

        return $this;
    }

    /**
     * Remove payments
     *
     * @param \SLN\RegisterBundle\Entity\UserPayment $payments
     */
    public function removePayment(\SLN\RegisterBundle\Entity\UserPayment $payments)
    {
        $this->payments->removeElement($payments);
    }

    /**
     * Get payments
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getPayments()
    {
        return $this->payments;
    }
}
