<?php
/**
  * Select one or more licensees, filtered from groups.
  *
  * @author Cédric Airaud
  */

namespace SLN\RegisterBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ExecutionContextInterface;

use SLN\RegisterBundle\Entity\Groupe;
use SLN\RegisterBundle\Entity\Licensee;

class LicenseeSelect {

    /**
     * @var Groupe $groupe Group to select licensees from
     */
    protected $groupe;

    /**
     * @var Licensee[] $licensees List of selected licensees
     *
     * @Assert\Count(
     *      min = "1",
     *      minMessage = "Vous devez sélectionner au moins 1 licencié",
     * )
     */
    public $licensees;

    const DEFAULT_TITLE = "Titre du mail";

    /**
     * @var string $title Title of the mail 
     *
     * * @Assert\NotEqualTo(
     *     value = LicenseeSelect::DEFAULT_TITLE,
     *     message = "Vous n'avez pas changé le titre du message"
     * )
     */
    public $title;


    const DEFAULT_BODY = "Cliquez directement sur le titre et le texte pour éditer le message à envoyer";

    /**
     * @var string $body Body of the mail 
     */
    public $body;


    /** 
     * Validate the body part in a callback function
     * @Assert\Callback
     */
    public function validate(ExecutionContextInterface $context)
    {
        // Vérifie si le nom est bidon
        if (strpos($this->body, self::DEFAULT_BODY) !== false) {
           $context->addViolationAt(
                'body',
                "Vous n'avez pas modifié le texte du message",
                array(),
                null
            );
        }
    }


    /** @ignore */
    public function __construct() {
        $this->licensees = array();
        $this->title = self::DEFAULT_TITLE;
        $this->body = self::DEFAULT_BODY;
    }

    /**
     * Set the groupe
     *
     * @param Groupe $groupe Groupe to set
     */
    public function setGroupe($groupe) { $this->groupe = $groupe;  }

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

