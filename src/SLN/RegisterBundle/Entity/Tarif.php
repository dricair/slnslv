<?php
/**
  * Tarif class, representing a price for a Groupe
  *
  * @author Cédric Airaud
  */


namespace SLN\RegisterBundle\Entity;
use SLN\RegisterBundle\Form\DataTransformer\PriceTransformer;

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
     */
    protected $value;

    /**
     * @var string $description Description for the tarif
     */
    public $description;

    /**
     * Constructor of the class
     *
     */
    public function __construct($type=self::TYPE_GLOBAL, $value=0, $description="") {
        $this->type = $type;
        $this->value = $value;
        $this->description = $description;
    }

    /**
     * Return type as a string
     * @return string
     */
    public function getTypeStr() {
        $values = $this->getTypes();
        return $values[$this->type];
    }

    /**
     * Return value as a price
     * @return string
     */
    public function getPrice() {
        $t = new PriceTransformer();
        return $t->transform($this->value);
    }
}


