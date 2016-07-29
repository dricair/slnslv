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
    const TYPE_GLOBAL        = 0;
    const TYPE_1DAY          = 1;
    const TYPE_EQUIPMENT     = 10;

    // Extra
    const TYPE_ST_LAURENT    = 20;
    const TYPE_REDUC_FAMILY2 = 21;
    const TYPE_REDUC_FAMILY3 = 22;
    const TYPE_REDUC_FAMILY4 = 23;
    const TYPE_REDUC_YEAR    = 24;
    const TYPE_REDUC_MANAGT  = 25;

    public static function getTypes($extra=TRUE) {
        $values = array(self::TYPE_GLOBAL => "Tarif global",
                        self::TYPE_1DAY   => "Tarif pour 1 seul jour",
                        self::TYPE_EQUIPMENT => "Equipement",
                       );

        if ($extra) {
            $values[self::TYPE_ST_LAURENT]    = "Majoration non-laurentin";
            $values[self::TYPE_REDUC_FAMILY2] = "Réduction famille 2ème";
            $values[self::TYPE_REDUC_FAMILY3] = "Réduction famille 3ème";
            $values[self::TYPE_REDUC_FAMILY4] = "Réduction famille 4ème ou +";
            $values[self::TYPE_REDUC_YEAR]    = "Adhésion en cours d'année";
            $values[self::TYPE_REDUC_MANAGT]  = "Réduction membre du bureau";
        }

        return $values;
    }


    /**
     * @var int $type Type of line
     */
    public $type;

    /**
     * @var string $value Price value
     * This is stored as an integer, with value * 100. For example 1.00€ is stored as 100
     */
    public $value;

    /**
     * @var string $description Description for the tarif
     */
    public $description;

    /**
     * Constructor of the class
     *
     * @param int $type: Type for the tarif
     * @param int $value: Default value. This has special meaning for some types (See below)
     * @param string $description: Optional description
     *
     * For TYPE_ST_LAURENT, value is not used
     * For REDUC types, value is the tarif value the reduction should be applied
     *
     */
    public function __construct($type=self::TYPE_GLOBAL, $value=0, $description="") {
        $this->type = $type;
        $this->value = $value;
        $this->description = $description;

        switch($this->type) {
            case self::TYPE_ST_LAURENT:    $this->value = 10 * 100; break;
            case self::TYPE_REDUC_FAMILY2: $this->value = round($value * -0.1); $this->description = "10%"; break;
            case self::TYPE_REDUC_FAMILY3: $this->value = round($value * -0.2); $this->description = "20%"; break;
            case self::TYPE_REDUC_FAMILY4: $this->value = round($value * -0.3); $this->description = "30%"; break;
            case self::TYPE_REDUC_YEAR:    break;
            case self::TYPE_REDUC_MANAGT:  $this->value = round($value * -0.65); $this->description = "65%"; break;
        }
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


