<?php
/**
  * Represents a group, with time slots and descriptions
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

use SLN\RegisterBundle\Entity\Horaire;
use SLN\RegisterBundle\Entity\Tarif;


// Compare horaire types, by day then by time
function cmpHoraire($a, $b) {
    if ($a['jour'] != $b['jour']) 
        return $a['jour'] - $b['jour'];
    return $a['debut'] - $b['debut'];
};


/**
 * Groupe class
 *
 * @ORM\Entity(repositoryClass="SLN\RegisterBundle\Entity\Repository\GroupeRepository")
 * @ORM\Table(name="groupe")
 * @ORM\HasLifecycleCallbacks()
 * @ExclusionPolicy("all")
 */
 class Groupe {
    /**
     * @var int $id Id of the groupe
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Expose
     */
    protected $id;

    /**
     * @var string $nom Name of the groupe
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
     * @var string $description Description for the Groupe
     * @ORM\Column(type="string", length=300)
     * @Assert\Length(
     *     max="300",
     *     maxMessage="La description est trop longue.",
     *     groups={"Registration", "Profile"}
     * )
     * @Expose
     */
    protected $description;
     

    /**
     * @var int $category Category for the groupe
     * @ORM\Column(type="integer")
     * @Assert\Choice(callback = "getCategories", message="Merci de sélectionner la catégorie", groups={"Registration", "Profile"})
     */
    protected $categorie;

    const ECOLE=0;
    const COMPETITION=1;
    const LOISIR=2;

    /**
     * @var int $order Order to print to the screen
     * @ORM\Column(type="integer")
     */
    protected $order;

    
    /**
     * Return array to convert category values to strings
     * 
     * @return string[] list of categories
     */
    public static function getCategories() {
        return array(self::ECOLE => "Ecole de natation", 
                     self::COMPETITION => "Sections compétition",
                     self::LOISIR => "Ados et loisirs");
    }

    /**
     * @var Licensee[] $licensees List of related Licensee
     * @ORM\OneToMany(targetEntity="Licensee", mappedBy="groupe")
     */
    protected $licensees;

    /**
     * @var bool $multiple True if independent time slots
     * @ORM\Column(type="boolean")
     * @Expose
     */
    protected $multiple;

     /**
      * @var Horaire[] $horaires List of Horaire slots
      * @ORM\Column(type="json_array")
      */
    protected $horaires;

     /**
      * @var Tarif[] $tarifs List of Tarif
      * @ORM\Column(type="json_array")
      */
    protected $tarifs;


    /** @ignore */
    public function __toString() {
        return $this->nom;
    }


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

        $this->licensees = new ArrayCollection();
        $this->horaires = Array((array)new Horaire());
        $this->tarifs = Array((array)new Tarif());
        $this->multiple = false;

        // Default groupe used in Licensee
        $this->categorie = $this::ECOLE;
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
     * Add a new Horaire
     * @param Horaire $horaire
     */
    public function addHoraire($horaire) {
        $this->horaires[] = $horaire;
    }

    /** 
      * Remove an horaire
      * @param Horaire $horaire
      */
    public function removeHoraire($horaire) {
        $index = -1;
        foreach ($this->horaires as $i => $h) {
            if ($h['jour'] == $horaire['jour'] and
                $h['debut'] == $horaire['debut'] and
                $h['fin'] == $horaire['fin'] and
                $h['description'] == $horaire['description']) 
                $index = $i;
        };

        if ($index != -1)
            unset($this->horaires[$index]);
    }
 
    /**
     * Add a new Tarif
     * @param Tarif $tarif
     */
    public function addTarif($tarif) {
        $this->tarifs[] = $tarif;
    }

    /** 
      * Remove an tarif
      * @param Tarif $tarif
      */
    public function removeTarif($tarif) {
        $index = -1;
        foreach ($this->tarifs as $i => $t) {
            if ($t['type'] == $tarif['type'] and
                $t['value'] == $tarif['value']) 
                $index = $i;
        };

        if ($index != -1)
            unset($this->tarifs[$index]);
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
     * Force id. Only used to explode groupes by day in case of Multiple
     *
     * @return integer 
     */
    public function forceId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Set nom
     *
     * @param string $nom
     * @return Groupe
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
     * Set categorie
     *
     * @param integer $categorie
     * @return Groupe
     */
    public function setCategorie($categorie)
    {
        $this->categorie = $categorie;

        return $this;
    }

    /**
     * Get categorie
     *
     * @return integer 
     */
    public function getCategorie()
    {
        return $this->categorie;
    }

    /**
     * Get categorie name as a string
     * @return string Category
     * @VirtualProperty
     */
    public function getCategorieName() {
        $categories = $this->getCategories();
        return $categories[$this->categorie];
    }

    /**
     * Add licensees
     *
     * @param Licensee $licensees Licensee to add
     * @return Groupe Containing group
     */
    public function addLicensee(\SLN\RegisterBundle\Entity\Licensee $licensees)
    {
        $this->licensees[] = $licensees;

        return $this;
    }

    /**
     * Remove licensees
     *
     * @param Licensee $licensees Licensees to remove
     */
    public function removeLicensee(Licensee $licensees)
    {
        $this->licensees->removeElement($licensees);
    }

    /**
     * Get licensees
     *
     * @return Collection Collection of licensees
     */
    public function getLicensees()
    {
        return $this->licensees;
    }

    /**
     * Get horaires
     *
     * @return Horaire[] List of slots
     */
    public function getHoraires() {
        return $this->horaires;
    }

    /**
     * Get tarifs
     *
     * @return Tarif[] List of tarifs
     */
    public function getTarifs() {
        return $this->tarifs;
    }

    /**
     * Get horaires as Horaire list
     *
     * @return Horaire[]
     */
    public function getFormatedHoraires() {
        $ret = array();

        usort($this->horaires, "SLN\RegisterBundle\Entity\cmpHoraire");
        foreach ($this->horaires as $horaire) {
            $ret[] = new Horaire($horaire['jour'], $horaire['debut'], $horaire['fin'], $horaire['description']);
        }
        return $ret;
    }

    /**
      * Virtual property for horaires.
      * 'jour' is reported as a string, 'debut' and 'fin' as formatted times
      *
      * @return string[] List of slots as string
      *
      * @VirtualProperty
      */
    public function horaireList() {
        $ret = array();
        foreach($this->getFormatedHoraires() as $horaire) {
            $ret[] = array("jour" => ucfirst($horaire->getJour()),
                           "debut" => $horaire->getDebut(),
                           "fin" => $horaire->getFin(),
                           "description" => $horaire->description);
        }
        return $ret;
    }

    /**
     * Get tarif as Tarif list
     *
     * @return Tarif[]
     */
    public function getTarifList() {
        $ret = array();

        foreach ($this->tarifs as $tarif) {
            $ret[] = new Tarif($tarif['type'], $tarif['value'], $tarif['description']);
        }
        return $ret;
    }

    /**
     * Virtual property for Multiple field
     *
     * Return None if multiple if false
     * Return a list of 'jour' indexes if multiple is true
     *
     * @return int[]|null List of 'jour' indexes if multiple, else null
     *
     * @VirtualProperty
     */
    public function multipleList() {
        if (! $this->multiple) return null;

        $ret = array();
        foreach($this->horaires as $horaire) {
            $ret[] = $horaire['jour'];
        }
        return $ret;
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
     * Set updated
     *
     * @param \DateTime $updated
     * @return Groupe
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
     * Set description
     *
     * @param string $description
     * @return Groupe
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string 
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set horaires
     *
     * @param array $horaires
     * @return Groupe
     */
    public function setHoraires($horaires)
    {
        $this->horaires = $horaires;

        return $this;
    }

    /**
     * Set tarifs
     *
     * @param array $tarifs
     * @return Groupe
     */
    public function setTarifs($tarifs)
    {
        $this->tarifs = $tarifs;

        return $this;
    }

    /**
     * Set multiple
     *
     * @param boolean $multiple
     * @return Groupe
     */
    public function setMultiple($multiple)
    {
        $this->multiple = $multiple;

        return $this;
    }

    /**
     * Get multiple
     *
     * @return boolean 
     */
    public function getMultiple()
    {
        return $this->multiple;
    }

    /**
     * Set order
     *
     * @param integer $order
     * @return Groupe
     */
    public function setOrder($order)
    {
        $this->order = $order;

        return $this;
    }

    /**
     * Get order
     *
     * @return integer 
     */
    public function getOrder()
    {
        return $this->order;
    }
}
