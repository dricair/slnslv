<?php

namespace SLN\RegisterBundle\Entity;

use FOS\UserBundle\Model\User as BaseUser;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

use SLN\RegisterBundle\Entity\Licensee;

/**
 * @ORM\Entity(repositoryClass="SLN\RegisterBundle\Entity\Repository\UserRepository")
 * @ORM\Table(name="sln_user")
 * @ORM\HasLifecycleCallbacks()
 */
class User extends BaseUser
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="integer")
     * TODO Does not work don't know why: Assert\Choice(callback = "getTitres", message="Merci de sélectionner une valeur", groups={"Registration", "Profile"})
     */
    protected $titre;
    
    const MR = 0;
    const MME = 1;

    public static function getTitres() {
        return array(self::MR => "M.", self::MME => "Mme");
    }

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
     * @ORM\Column(type="string", length=100)
     *
     @Assert\NotBlank(message="Merci d'entrer un prénom.", groups={"Registration", "Profile"})
     * @Assert\Length(
     *     min=3,
     *     max="100",
     *     minMessage="Le prénom est trop court.",
     *     maxMessage="Le prénom est trop long.",
     *     groups={"Registration", "Profile"}
     * )
     */
    protected $prenom;

    /**
     * @ORM\Column(type="text", length=300)
     *
     @Assert\NotBlank(message="Merci d'entrer votre adresse.", groups={"Registration", "Profile"})
     */
    protected $adresse;

    /**
     * @ORM\Column(type="string", length=10)
     *
     * @Assert\NotBlank(message="Merci d'entrer le code postal.", groups={"Registration", "Profile"})
     * @Assert\Regex("/^\d{5}/")
     */
    protected $code_postal;

    /**
     * @ORM\Column(type="string", length=100)
     *
     @Assert\NotBlank(message="Merci d'entrer la ville.", groups={"Registration", "Profile"})
     */
    protected $ville;
        
    /**
     * @ORM\Column(type="string", length=20, nullable=True)
     * @Assert\Regex("/^[0-9-.+]+/")
     */
    protected $tel_domicile;

    /**
     * @ORM\Column(type="string", length=20, nullable=True)
     * @Assert\Regex("/^[0-9-.+]+/")
     */
    protected $tel_portable;

    /**
     * @Assert\Callback
     */
    public function validate(ExecutionContextInterface $context)
    {
        // Check that at least one telephone number is given
        if ($this->tel_domicile == NULL and $this->tel_portable == NULL) {
            $context->buildViolation('Merci de spécifier au moins un numéro de téléphone, de préférence portable.')
                ->atPath('tel_portable')
                ->addViolation();
        }
    }


    /**
     * @ORM\Column(type="datetime")
     */
    protected $created;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $updated;

    /**
     * @ORM\OneToMany(targetEntity="Licensee", mappedBy="user")
     */
    protected $licensees;


    public function __construct()
    {
        parent::__construct();

        $this->setCreated(new \DateTime());
        $this->setUpdated(new \DateTime());

        $this->licensees = new ArrayCollection();
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
     * @return User
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
     * Set prenom
     *
     * @param string $prenom
     * @return User
     */
    public function setPrenom($prenom)
    {
        $this->prenom = $prenom;

        return $this;
    }

    /**
     * Get prenom
     *
     * @return string 
     */
    public function getPrenom()
    {
        return $this->prenom;
    }

    /**
     * Set adresse
     *
     * @param string $adresse
     * @return User
     */
    public function setAdresse($adresse)
    {
        $this->adresse = $adresse;

        return $this;
    }

    /**
     * Get adresse
     *
     * @return string 
     */
    public function getAdresse()
    {
        return $this->adresse;
    }

    /**
     * Set code_postal
     *
     * @param string $codePostal
     * @return User
     */
    public function setCodePostal($codePostal)
    {
        $this->code_postal = $codePostal;

        return $this;
    }

    /**
     * Get code_postal
     *
     * @return string 
     */
    public function getCodePostal()
    {
        return $this->code_postal;
    }

    /**
     * Set ville
     *
     * @param string $ville
     * @return User
     */
    public function setVille($ville)
    {
        $this->ville = $ville;

        return $this;
    }

    /**
     * Get ville
     *
     * @return string 
     */
    public function getVille()
    {
        return $this->ville;
    }

    /**
     * Set tel_domicile
     *
     * @param string $telDomicile
     * @return User
     */
    public function setTelDomicile($telDomicile)
    {
        $this->tel_domicile = $telDomicile;

        return $this;
    }

    /**
     * Get tel_domicile
     *
     * @return string 
     */
    public function getTelDomicile()
    {
        return $this->tel_domicile;
    }

    /**
     * Set tel_portable
     *
     * @param string $telPortable
     * @return User
     */
    public function setTelPortable($telPortable)
    {
        $this->tel_portable = $telPortable;

        return $this;
    }

    /**
     * Get tel_portable
     *
     * @return string 
     */
    public function getTelPortable()
    {
        return $this->tel_portable;
    }

    /**
     * Set titre
     *
     * @param integer $titre
     * @return User
     */
    public function setTitre($titre)
    {
        $this->titre = $titre;

        return $this;
    }

    /**
     * Get titre
     *
     * @return integer 
     */
    public function getTitre()
    {
        return $this->titre;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     * @return User
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
     * @return User
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
     * Add licensees
     *
     * @param Licensee $licensees
     * @return User
     */
    public function addLicensee(Licensee $licensees)
    {
        $this->licensees[] = $licensees;

        return $this;
    }

    /**
     * Remove licensees
     *
     * @param Licensee $licensees
     */
    public function removeLicensee(Licensee $licensees)
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
}
