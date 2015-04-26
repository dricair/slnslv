<?php

namespace SLN\RegisterBundle\Entity;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;


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


    public function __construct() {
        $this->setCreated(new \DateTime());
        $this->setUpdated(new \DateTime());

        $this->licensees = new ArrayCollection();
        $this->horaires = Array();
    }

    /**
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
     * Get horaires
     *
     * @return array 
     */
    public function getHoraires()
    {
        return $this->horaires;
    }
}
