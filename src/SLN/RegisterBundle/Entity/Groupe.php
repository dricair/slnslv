<?php

namespace SLN\RegisterBundle\Entity;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use SLN\RegisterBundle\Entity\Horaire;

class Horaire {
    public $jour;
    public $debut;
    public $fin;
    public $description;

    public function __construct($jour=0, $debut=0, $fin=0, $description="") {
        $this->jour = $jour;
        $this->debut = $debut;
        $this->fin = $fin;
        $this->description = $description;
    }

    public static function getJours() {
        return array(0 => "lundi",
                     1 => "mardi",
                     2 => "mercredi",
                     3 => "jeudi",
                     4 => "vendredi",
                     5 => "samedi",
                     6 => "dimanche");
    }

    public function getJour() { return $this->getjours()[$this->jour]; }

    public function getDebut() { return date("H:i", $this->debut); }
    public function getFin() { return date("H:i", $this->fin); }
}


/**
 * @ORM\Entity(repositoryClass="SLN\RegisterBundle\Entity\Repository\GroupeRepository")
 * @ORM\Table(name="groupe")
 * @ORM\HasLifecycleCallbacks()
 */
 class Groupe {
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=100)
     *
     @Assert\NotBlank(message="Merci d'entrer un nom.", groups={"Registration", "Profile"})
     * @Assert\Length(
     *     min=3,
     *     max="100",
     *     minMessage="Le nom est trop court.",
     *     maxMessage="Le nom est trop long.",
     *     groups={"Registration", "Profile"}
     * )
     */
    protected $nom;

    /**
     * @ORM\Column(type="string", length=300)
     * @Assert\Length(
     *     max="300",
     *     maxMessage="La description est trop longue.",
     *     groups={"Registration", "Profile"}
     * )
     */
    protected $description;
     

    /**
     * @ORM\Column(type="integer")
    @Assert\Choice(callback = "getCategories", message="Merci de sélectionner la catégorie", groups={"Registration", "Profile"})
     */
    protected $categorie;
    
    public static function getCategories() {
        return array(0 => "Ecole de natation", 
                     1 => "Sections compétition",
                     2 => "Ados et loisirs");
    }

    /**
     * @ORM\OneToMany(targetEntity="Licensee", mappedBy="groupe")
     */
    protected $licensees;

     /**
     * @ORM\Column(type="json_array")
     */
    protected $horaires;

    // TODO: Tarifs


    /*
     * @ORM\Column(type="datetime")
     */
    protected $created;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $updated;

    public function __construct() {
        $this->setCreated(new \DateTime());
        $this->setUpdated(new \DateTime());

        $this->licensees = new ArrayCollection();
        $this->horaires = Array((array)new Horaire(), (array)new Horaire());
    }

    /**
     * @ORM\PreUpdate
     */
    public function setUpdatedValue()
    {
       $this->setUpdated(new \DateTime());
    }

    /**
     * Add a new Horaire
     */
    public function addHoraire($horaire) {
        $this->horaires[] = $horaire;
    }

    /** 
      * Remove an horaire
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
     * Add licensees
     *
     * @param \SLN\RegisterBundle\Entity\Licensee $licensees
     * @return Groupe
     */
    public function addLicensee(\SLN\RegisterBundle\Entity\Licensee $licensees)
    {
        $this->licensees[] = $licensees;

        return $this;
    }

    /**
     * Remove licensees
     *
     * @param \SLN\RegisterBundle\Entity\Licensee $licensees
     */
    public function removeLicensee(\SLN\RegisterBundle\Entity\Licensee $licensees)
    {
        $this->licensees->removeElement($licensees);
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
     * Get horaires
     *
     * @return array 
     */
    public function getHoraires() {
        return $this->horaires;
    }

    public function getFormatedHoraires() {
        $ret = array();
        foreach ($this->horaires as $horaire) {
            $ret[] = new Horaire($horaire['jour'], $horaire['debut'], $horaire['fin'], $horaire['description']);
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
}
