<?php
/**
  * Test specific functions of the Groupe and Horaire classes
  *
  * @author Cédric Airaud
  */

namespace SLN\RegisterBundle\Tests\Entity;

use SLN\RegisterBundle\Entity\Horaire;
use SLN\RegisterBundle\Entity\Groupe;

/**
 * Test the Horaire class
 */
class HoraireTest extends \PHPUnit_Framework_TestCase {
    /**
     * Test the getJour function
     */
    public function testGetJour() {
        foreach (array("lundi", "mardi", "mercredi", "jeudi", "vendredi", "samedi", "dimanche") as $index=>$value) {
            $horaire = new Horaire($index);
            $this->assertEquals($value, $horaire->getJour());
        }
    }


    /**
     * Test the getDebut function
     */
    public function testGetDebut() {
        foreach (array("01:00" => mktime(1,  00), 
                       "09:15" => mktime(9,  15),
                       "12:35" => mktime(12, 35),
                       "18:58" => mktime(18, 58)) as $str => $value) {
            $horaire = new Horaire(0, $value);
            $this->assertEquals($str,  $horaire->getDebut());
        }
    }

    /**
     * Test the getFin function
     */
    public function testGetFin() {
        foreach (array("01:00" => mktime(1,  00), 
                       "09:15" => mktime(9,  15),
                       "12:35" => mktime(12, 35),
                       "18:58" => mktime(18, 58)) as $str => $value) {
            $horaire = new Horaire(0, 0, $value);
            $this->assertEquals($str,  $horaire->getFin());
        }
    }
}

