<?php
/**
  * Payment/Reduction for a User
  *
  * @author Cédric Airaud
  */
 
namespace SLN\RegisterBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

use SLN\RegisterBundle\Entity\User;
use SLN\RegisterBundle\Entity\Saison;
use SLN\RegisterBundle\Form\DataTransformer\PriceTransformer;


/**
 * LicenseePayment class, representing an payment or refund.
 *
 * @ORM\Entity(repositoryClass="SLN\RegisterBundle\Entity\Repository\UserPaymentRepository")
 * @ORM\Table(name="payment")
 * @ORM\HasLifecycleCallbacks()
 */
class UserPayment {
     
    /**
     * @var int $id Id of the payment
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * var int $ptype Integer type of Payment / Refund
     * @ORM\Column(type="integer")
     */
    protected $ptype;
    
    const CHEQUE   = 0;
    const VACANCES = 1;
    const LIQUIDE  = 2;
    const COUPON   = 3;
    const LICENCE  = 4;
    const FAMILLE  = 5;
    const BUREAU   = 6;

    /**
     * Return an array to convert integer to string for type
     *
     * @param  int $stype: 0 => all types, 1 => payment types, 2 => reduction types
     * @return string[] List of string for type.
     */
    public static function getTypes($stype=0) {
        $v = array();
        if ($stype == 0 or $stype == 2) {
          $v[self::LICENCE] = "Déjà licencié";
          $v[self::FAMILLE] = "Réduction famille";
          $v[self::BUREAU]  = "Réduction membre du bureau";
        }

        if ($stype == 0 or $stype == 1) {
          $v[self::CHEQUE]   = "Chèque";
          $v[self::VACANCES] = "Chèque(s) vacances";
          $v[self::LIQUIDE]  = "Liquide";
          $v[self::COUPON]   = "Coupon(s) sport";
        }

        return $v;
    }

    /**
     * @var string $description Description or reference
     * @ORM\Column(type="string", length=100, nullable=True)
     */
    protected $description;

    /**
     * Validate the values and throws an exception if this is not correct.
     * @param ExecutionContextInterface $context Context
     * @Assert\Callback
     */
    public function validate(ExecutionContextInterface $context)
    {
        if ($this->ptype == self::CHEQUE and preg_match("/^\w+\s*-\s*\w+\s+\d+$/", $this->description) != 1) {
            $context->buildViolation('Le numéro de chèque doit être spécifié sous la forme: <NOM> - <BANQUE> <numéro>, avec <numéro> en chiffres.')
                ->atPath('description')
                ->addViolation();
        }
    }

    /**
     * @var string $value Price value
     * This is stored as an integer, with value * 100. For example 1.00€ is stored as 100
     * @ORM\Column(type="integer")
     * @Assert\NotBlank(message="Merci d'entrer une valeur.")
     */
    protected $value;


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
     * @var User $user Connected User class
     * @ORM\ManyToOne(targetEntity="User", inversedBy="payments")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     * @Assert\NotBlank(message="Un paiement doit être rattaché à un utilisateur.")
     */
    protected $user;

    /**
     * @var Saison $saison_id Corresponding saison. 
     * @ORM\ManyToOne(targetEntity="Saison", inversedBy="payments")
     * @ORM\JoinColumn(name="saison_id", referencedColumnName="id")
     * @Assert\NotBlank(message="Un paiement doit être rattaché à une saison.")
     */
    protected $saison;


    /** @ignore */
    public function __construct()
    {
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
     * Set ptype
     *
     * @param integer $ptype
     * @return LicenseePayment
     */
    public function setPtype($ptype)
    {
        $this->ptype = $ptype;

        return $this;
    }

    /**
     * Get ptype
     *
     * @return integer 
     */
    public function getPtype()
    {
        return $this->ptype;
    }

    /**
     * Get ptype as a string
     *
     * @return string
     */
    public function getPtypeStr()
    {
        $values = self::getTypes();
        return $values[$this->ptype];
    }

    /**
     * Set description
     *
     * @param string $description
     * @return LicenseePayment
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
     * Set value
     *
     * @param integer $value
     * @return LicenseePayment
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get value
     *
     * @return integer 
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Get value as a string
     *
     * @return string
     */
    public function getValueStr()
    {
        $t = new PriceTransformer();
        return $t->transform($this->value);
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     * @return LicenseePayment
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
     * @return LicenseePayment
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
     * @param \SLN\RegisterBundle\Entity\User $user
     * @return UserPayment
     */
    public function setUser(\SLN\RegisterBundle\Entity\User $user = null)
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

    /**
     * Set saison
     *
     * @param \SLN\RegisterBundle\Entity\Saison $saison
     * @return UserPayment
     */
    public function setSaison(\SLN\RegisterBundle\Entity\Saison $saison = null)
    {
        $this->saison = $saison;

        return $this;
    }

    /**
     * Get saison
     *
     * @return \SLN\RegisterBundle\Entity\Saison 
     */
    public function getSaison()
    {
        return $this->saison;
    }
}
