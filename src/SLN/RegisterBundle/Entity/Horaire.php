<?php
/**
  * Horaire class, representing a time slot with description
  *
  * @author Cédric Airaud
  */


namespace SLN\RegisterBundle\Entity;


/**
 * Class for time slot, used in Horaire class
 */
class Horaire {
    /**
     * @var int $jour Day of the week
     */
    public $jour;

    /**
     * @var \Datetime $debut Start time
     */
    public $debut;

    /**
     * @var \Datetime $debut End time
     */
    public $fin;

    /**
     * @var string $description Description for the slot
     */
    public $description;

    /**
     * Constructor of the class
     *
     * @param int        $jour        Day of the week
     * @param timestamp  $debut       Start time
     * @param timestamp  $fin         End time
     * @param string     $description Description for the slot
     */
    public function __construct($jour=0, $debut=0, $fin=0, $description="") {
        $this->jour = $jour;
        $this->debut = $debut;
        $this->fin = $fin;
        $this->description = $description;
    }

    /**
     * Returns a list to convert days of week to strings
     *
     * @return string[] List of days
     */
    public static function getJours() {
        return array(0 => "lundi",
                     1 => "mardi",
                     2 => "mercredi",
                     3 => "jeudi",
                     4 => "vendredi",
                     5 => "samedi",
                     6 => "dimanche");
    }

    /**
     * Return the day of week as a string
     *
     * @return string Day of week as a string
     */
    public function getJour() { $jours = $this->getjours(); return $jours[$this->jour]; }

    /**
     * Return start time as a string
     * 
     * @return string Start time
     */
    public function getDebut() { return date("H:i", $this->debut); }

    /**
     * Return end time as a string
     * 
     * @return string End time as a string
     */
    public function getFin() { return date("H:i", $this->fin); }
}
