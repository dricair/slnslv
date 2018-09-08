<?php
/**
  * Link between a Licensee and a Saison
  * Save the state of the inscription to the given Saison.
  *
  * @author Cédric Airaud
  */

namespace SLN\RegisterBundle\Entity;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation\ExclusionPolicy,
    JMS\Serializer\Annotation\Expose,
    JMS\Serializer\Annotation\Groups,
    JMS\Serializer\Annotation\VirtualProperty;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Constraints as Assert;


use SLN\RegisterBundle\Entity\Licensee;
use SLN\RegisterBundle\Entity\Saison;
use SLN\RegisterBundle\Entity\Groupe;

/**
 * LicenseeSaison class
 *
 * @ORM\Entity(repositoryClass="SLN\RegisterBundle\Entity\Repository\LicenseeSaisonRepository")
 * @ORM\Table(name="licensee_saison")
 * @ExclusionPolicy("all")
 */
class LicenseeSaison {
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Expose
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Licensee", inversedBy="saison_links")
     * @ORM\JoinColumn(name="licensee_id", referencedColumnName="id")
     */
    protected $licensee;

    /**
     * @ORM\ManyToOne(targetEntity="Saison", inversedBy="licensee_links")
     * @ORM\JoinColumn(name="saison_id", referencedColumnName="id")
     */
    protected $saison;

    /**
     * @var \Datetime $start Licensee date
     * @ORM\Column(type="datetime")
     */
    protected $start;

    /**
     * @var Groupe $groupe Selected groupe
     * @ORM\ManyToOne(targetEntity="Groupe", inversedBy="licensees")
     * @ORM\JoinColumn(name="groupe_id", referencedColumnName="id", nullable=True)
     */
    protected $groupe;

    /**
     * @var Groupe $new_groupe Groupe for next saison. Can be null.
     * @ORM\ManyToOne(targetEntity="Groupe")
     * @ORM\JoinColumn(name="groupe_new_id", referencedColumnName="id", nullable=True)
     */
    protected $new_groupe;

    /**
     * @var bool[] $groupe_jours Selected days for the groups
     * @ORM\Column(type="array")
     */
    protected $groupe_jours;

    /**
     * var bool[] $inscription State for inscription
     * @ORM\Column(type="array")
     */
    protected $inscription;

    const FEUILLE=0;
    const CERTIFICAT=2;
    const PAIEMENT=3;
    const LICENCE=4;

    /**
     * Return an array of the possible inscription states
     *
     * @return string[] List of strings for inscription
     */
    public static function getInscriptionNames() {
        return array(self::FEUILLE => "Inscription",  
                     self::CERTIFICAT => "Certificat médical", 
                     self::PAIEMENT => "Paiement total",
                     self::LICENCE => "Licence");
    }


    /**
     * Change value for an inscription
     *
     * @param int $index: Inscription Index
     * @param int $missing: New missing value
     * @return int New missing value
     */
    public function setInscriptionMissing($index, $missing) {
        // certificat depends on the certificat ok or not
        if ($index == self::CERTIFICAT)
            $missing = !$this->certificatOk();

        if ($missing and in_array($index, $this->inscription)) {
            $key = array_search($index, $this->inscription);
            unset($this->inscription[$key]);
            $this->inscription = array_values($this->inscription);
        }

        if (!$missing and !in_array($index, $this->inscription)) {
            $this->inscription[] = $index;
        }

        return !in_array($index, $this->inscription);
    }

    /* 
     * Return true if certificat for licensee is ok (Less than 3 years
     * after saison start)
     */
    public function certificatOk() {
      $saison_start = $this->saison->getStart();
      $certificat = $this->licensee->getCertificat();
      return $certificat and $certificat->diff($saison_start)->y < 3;
    }

    /**
     * Validate the values and throws an exception if this is not correct.
     * @param ExecutionContextInterface $context Context
     * @Assert\Callback
     */
    public function validate(ExecutionContextInterface $context)
    {
        // Check that at least one day is selected when group is 'Multiple'
        if ($this->groupe and $this->groupe->getMultiple()) {
            $found = false;
            $list_jours = $this->groupe->multipleList();
            foreach ($this->groupe_jours as $jour) {
                if (in_array($jour, $list_jours)) $found = true;
            }

            if (!$found) 
              $context->buildViolation('Veuillez sélectionner au moins un jour.')
                  ->atPath('groupe_jours')
                  ->addViolation();
        }
    }


    /** @ignore */
    public function __construct() {
        $this->setStart(new \DateTime());
        $this->inscription = array();
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
     * Set licensee
     *
     * @param Licensee $licensee
     * @return LicenseeSaison
     */
    public function setLicensee(Licensee $licensee = null)
    {
        $this->licensee = $licensee;

        return $this;
    }

    /**
     * Get licensee
     *
     * @return Licensee 
     */
    public function getLicensee()
    {
        return $this->licensee;
    }

    /**
     * Set saison
     *
     * @param Saison $saison
     * @return LicenseeSaison
     */
    public function setSaison(Saison $saison = null)
    {
        $this->saison = $saison;

        return $this;
    }

    /**
     * Get saison
     *
     * @return Saison 
     */
    public function getSaison()
    {
        return $this->saison;
    }

    /**
     * Set start
     *
     * @param \DateTime $start
     * @return LicenseeSaison
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
     * Set inscription
     *
     * @param array $inscription
     * @return LicenseeSaison
     */
    public function setInscription($inscription)
    {
        $this->inscription = $inscription;

        return $this;
    }

    /**
     * Add inscription
     *
     * @param $inscription_item
     * @return LicenseeSaison
     */
    public function addInscription($inscription_item)
    {
        if (!in_array($inscription_item, $this->inscription))
            $this->inscription[] = $inscription_item;

        return $this;
    }


    /**
     * Get inscription
     *
     * @return array 
     */
    public function getInscription()
    {
        // Certificat Ok depends on certificat date
        $this->setInscriptionMissing(self::CERTIFICAT, NULL /* Unused */);
        return $this->inscription;
    }


    /**
     * Set groupe_jours
     *
     * @param array $groupeJours
     * @return LicenseeSaison
     */
    public function setGroupeJours($groupeJours)
    {
        $this->groupe_jours = $groupeJours;

        return $this;
    }

    /**
     * Get groupe_jours
     *
     * @return array 
     */
    public function getGroupeJours()
    {
        return $this->groupe_jours;
    }

    /**
     * Set groupe
     *
     * @param Groupe $groupe
     * @return LicenseeSaison
     */
    public function setGroupe(Groupe $groupe = null)
    {
        $this->groupe = $groupe;

        return $this;
    }

    /**
     * Get groupe
     *
     * @return Groupe 
     */
    public function getGroupe()
    {
        return $this->groupe;
    }

    /**
     * Set new_groupe
     *
     * @param \SLN\RegisterBundle\Entity\Groupe $newGroupe
     * @return LicenseeSaison
     */
    public function setNewGroupe(\SLN\RegisterBundle\Entity\Groupe $newGroupe = null)
    {
        $this->new_groupe = $newGroupe;

        return $this;
    }

    /**
     * Get new_groupe
     *
     * @return \SLN\RegisterBundle\Entity\Groupe 
     */
    public function getNewGroupe()
    {
        return $this->new_groupe;
    }
}
