<?php
/**
  * Select one or more licensees, filtered from groups.
  *
  * @author Cédric Airaud
  */

namespace SLN\RegisterBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;

use SLN\RegisterBundle\Entity\Groupe;
use SLN\RegisterBundle\Entity\Licensee;

class LicenseeSelect {

    /**
     * @var Groupe $groupe Group to select licensees from
     */
    protected $groupe;

    /**
     * @var Licensee[] $licensees List of selected licensees
     */
    public $licensees;


    /** @ignore */
    public function __construct() {
        $this->licensees = array();
    }

    /**
     * Set the groupe
     *
     * @param Groupe $groupe Groupe to set
     */
    public function setGroupe(Groupe $groupe) { $this->groupe = $groupe;  }

    /**
     * Get the groupe
     *
     * @return Groupe
     */
    public function getGroupe() { return $this->groupe; }

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
     * Set licensees
     *
     * @param Licensee[] $licensees
     */
    public function setLicensees(array $licensees) {
        $this->licensees = array();
        foreach($licensees as $licensee)
            $this->addLicensee($licensee);
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

