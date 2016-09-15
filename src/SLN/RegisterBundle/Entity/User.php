<?php
/**
  * Represents a user, used for connection. A user contains Licensee
  *
  * @author Cédric Airaud
  */

namespace SLN\RegisterBundle\Entity;

use FOS\UserBundle\Model\User as BaseUser;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

use SLN\RegisterBundle\Entity\Licensee;
use SLN\RegisterBundle\Entity\UserPayment;
use SLN\RegisterBundle\Entity\Tarif;

use SLN\RegisterBundle\Form\DataTransformer\PriceTransformer;



/**
 * User class, derived from FOS User
 * @ORM\Entity(repositoryClass="SLN\RegisterBundle\Entity\Repository\UserRepository")
 * @ORM\Table(name="sln_user")
 * @ORM\HasLifecycleCallbacks()
 */
class User extends BaseUser
{
    /**
     * @var int $id Index in the DB
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var int $titre Title value as integer
     * @ORM\Column(type="integer")
     * @todo Does not work don't know why: Assert\Choice(callback = "getTitres", message="Merci de sélectionner une valeur", groups={"Registration", "Profile"})
     */
    protected $titre;
    
    const MR = 0;
    const MME = 1;

    /**
     * Returns an array to convert Title integer to string
     *
     * @return string[] List of title strings
     */
    public static function getTitres() {
        return array(self::MR => "M.", self::MME => "Mme");
    }

    /**
     * @var string $nom Family name of the user
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
     */
    protected $nom;

    /**
     * @var string $prenom First name of the user
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
     * @var string $adresse User address
     * @ORM\Column(type="text", length=300)
     *
     @Assert\NotBlank(message="Merci d'entrer votre adresse.", groups={"Registration", "Profile"})
     */
    protected $adresse;

    /**
     * @var string $code_postal Post code
     * @ORM\Column(type="string", length=10)
     *
     * @Assert\NotBlank(message="Merci d'entrer le code postal.", groups={"Registration", "Profile"})
     * @Assert\Regex("/^\d{5}/")
     */
    protected $code_postal;

    /**
     * @var string $ville Town
     * @ORM\Column(type="string", length=100)
     *
     * @Assert\NotBlank(message="Merci d'entrer la ville.", groups={"Registration", "Profile"})
     */
    protected $ville;
        
    /**
     * @var string $tel_domicile Home phone
     * @ORM\Column(type="string", length=20, nullable=True)
     * @Assert\Regex("/^[0-9-.+]+/")
     */
    protected $tel_domicile;

    /**
     * @var string $tel_domicile Mobile phone
     * @ORM\Column(type="string", length=20, nullable=True)
     * @Assert\Regex("/^[0-9-.+]+/")
     */
    protected $tel_portable;

    /**
     * Validate the values and throws an exception if this is not correct.
     * @param ExecutionContextInterface $context Context
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
     * @var string $secondary_email Secondary email
     * @ORM\Column(type="string", length=50, nullable=True)
     * @Assert\Email()
     */
    protected $secondary_email;

    /**
     * @var \Datetime $created Creation date
     * @ORM\Column(type="datetime")
     */
    protected $created;

    /**
     * @var \Datetime $updated Last update date
     * @ORM\Column(type="datetime")
     */
    protected $updated;

    /**
     * @var Licensee[] List of related licensees
     * @ORM\OneToMany(targetEntity="Licensee", mappedBy="user")
     */
    protected $licensees;

    /**
     * @var Payments[] List of related payments
     * @ORM\OneToMany(targetEntity="UserPayment", mappedBy="user")
     */
    protected $payments;


    /** @ignore */
    public function __construct()
    {
        parent::__construct();

        $this->setCreated(new \DateTime());
        $this->setUpdated(new \DateTime());

        $this->licensees = new ArrayCollection();
    }

    
    /** Sort function (Reverse order) */
    static function sort_cotisations($a, $b) {
        return $b["value"] - $a["value"];
    }

    /**
     * Add extra Tarif to each licensee
     */
    public function addExtraTarif() {
        $cotisations = array();

        foreach ($this->licensees as &$licensee) {
            $tarifs = $licensee->getTarifList();
            if ($licensee->getGroupe() !== NULL and intval($this->code_postal) != 6700) {
                $licensee->addTarif(new Tarif(Tarif::TYPE_ST_LAURENT)); 
            }

            foreach($tarifs as &$tarif) {
                if ($tarif->type == Tarif::TYPE_GLOBAL or
                    $tarif->type == Tarif::TYPE_1DAY) {

                    $fonctions = $licensee->getFonctions();
                    if ($fonctions and in_array(Licensee::BUREAU, $fonctions)) {
                        $licensee->addTarif(new Tarif(Tarif::TYPE_REDUC_MANAGT, $tarif->value));
                    } else {
                        $cotisations[] = array("value" => $tarif->value, "licensee" => &$licensee);
                    }
                }
            }
        }

        if (count($cotisations > 1)) {
            // Sort from higher value to smaller
            usort($cotisations, array("SLN\RegisterBundle\Entity\User", "sort_cotisations"));
            $num = 1;
            foreach ($cotisations as &$cotisation) {
                $type = NULL;
                switch($num) {
                  case 1:  break;
                  case 2:  $type = Tarif::TYPE_REDUC_FAMILY2; break;
                  case 3:  $type = Tarif::TYPE_REDUC_FAMILY3; break;
                  default: $type = Tarif::TYPE_REDUC_FAMILY4; break; 
                }

                if ($type) $cotisation["licensee"]->addTarif(new Tarif($type, $cotisation["value"]));
                $num++;
            }
        }
    }

    
    /**
     * Total of cotisations
     *
     * Assume that addExtraTarif() has been called first
     *
     * @return int Price value suitable for PriceTransformer
     */
    public function totalCotisations() {
        $total = 0;
        foreach ($this->licensees as &$licensee) {
            $tarifs = $licensee->getTarifList();
            foreach ($tarifs as &$tarif) {
               $total += $tarif->value; 
            }
        }
        return $total;
    }

    /**
     * Total of payments
     *
     * @return int Price value suitable for PriceTransformer
     */
    public function totalPayments() {
        $total = 0;
        foreach ($this->payments as &$payment) {
            $total += $payment->getValue();
        }
        return $total;
    }

    /**
     * Array of cotisation and payment information
     *
     * @return string[] Array of string descriptions
     */
    public function paymentInfo() {
        $total_cotisations = $this->totalCotisations();
        $total_payments = $this->totalPayments();
        $diff = $total_cotisations - $total_payments;
        $t = new PriceTransformer();

        return array("total_cotisations" => $t->transform($total_cotisations),
                     "total_payments"    => $t->transform($total_payments),
                     "diff_payments"     => $t->transform(abs($diff)),
                     "diff_value"        => $diff); 
    }


    /**
     * Return true if at least one licensee has Missing payment flag
     * This may not correspond to the total of payments and cotisations
     *
     * @return bool
     */
    public function licenseesMissingPayment() {
        foreach ($this->licensees as &$licensee) {
            if ($licensee->inscriptionMissingPayment())
                return TRUE;
        }
        return FALSE;
    }


    /**
     * String to use for choice lists
     *
     * @return string
     */
    public function __toString() {
        return "{$this->nom} {$this->prenom}";
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
     * Get titre as a string
     *
     * @return string
     */
    public function getTitreName() {
        $values = $this::getTitres();
        return $values[$this->titre];
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


    /**
     * Set secondary_email
     *
     * @param string $secondaryEmail
     * @return User
     */
    public function setSecondaryEmail($secondaryEmail)
    {
        $this->secondary_email = $secondaryEmail;

        return $this;
    }

    /**
     * Get secondary_email
     *
     * @return string 
     */
    public function getSecondaryEmail()
    {
        return $this->secondary_email;
    }

    /**
     * Add payments
     *
     * @param UserPayment $payments
     * @return User
     */
    public function addPayment(UserPayment $payments)
    {
        $this->payments[] = $payments;

        return $this;
    }

    /**
     * Remove payments
     *
     * @param UserPayment $payments
     */
    public function removePayment(UserPayment $payments)
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
