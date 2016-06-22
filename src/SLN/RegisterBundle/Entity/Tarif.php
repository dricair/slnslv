<?php
/**
  * Tarif class, representing a price for a Groupe
  *
  * @author Cédric Airaud
  */


namespace SLN\RegisterBundle\Entity;

/**
 * Class representing a tarif for a Groupe
 */
class Tarif {
    const TYPE_GLOBAL    = 0;
    const TYPE_1DAY      = 1;
    const TYPE_EQUIPMENT = 10;

    public function getTypes() {
        
        return array(self::TYPE_GLOBAL => "Tarif global",
                     self::TYPE_1DAY   => "Tarif pour 1 seul jour",
                     self::TYPE_EQUIPMENT => "Equipement",
                     );
    }

    /**
     * @var int $type Type of line
     */
    public $type;

    /**
     * @var string $value Price value
     * This is stored as an integer, with value * 100. For example 1.00€ is stored as 100
     * @ORM\Column(type="integer")
     * @Assert\NotBlank(message="Merci d'entrer une valeur.")
     */
    protected $value;

    /**
     * Constructor of the class
     *
     */
    public function __construct() {
        $this->type = self::TYPE_GLOBAL;
        $this->value = 0;
    }
}


