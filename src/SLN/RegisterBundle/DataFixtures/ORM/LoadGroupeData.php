<?php
/**
  * Sample data for tests: Groupe data 
  *
  * @author Cédric Airaud
  */

namespace SLN\RegisterBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use SLN\RegisterBundle\Entity\Groupe;
use SLN\RegisterBundle\Entity\Horaire;


/**
 * Load user data to database
 */
class LoadGroupeData extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * Load Groupe class data
     *
     * @param ObjectManager $manager Manager instance for load.
     *
     * @return void
     */
    public function load(ObjectManager $manager)
    {
        $groupe = new Groupe();
        $groupe->setNom("Otaries");
        $groupe->setDescription("Cours Otaries");
        $groupe->setCategorie(Groupe::ECOLE);
        $groupe->setOrder(3);
        $groupe->setMultiple(FALSE);
        $groupe->setCapacity(40);
        $groupe->addHoraire(new Horaire(0, 9, 11.5, "Cours 1"));
        $groupe->addHoraire(new Horaire(1, 12, 13,  "Cours 2"));
        
        $manager->persist($groupe);
        $manager->flush();
        $this->addReference("groupe-ecole", $groupe);
        
        $groupe = new Groupe();
        $groupe->setNom("Compétition");
        $groupe->setDescription("Cours Compétition");
        $groupe->setCategorie(Groupe::COMPETITION);
        $groupe->setOrder(2);
        $groupe->setMultiple(FALSE);
        $groupe->setCapacity(20);
        $groupe->addHoraire(new Horaire(2, 17, 18, "Cours 1"));
        $groupe->addHoraire(new Horaire(3, 11.5, 12,  "Cours 2"));
        
        $manager->persist($groupe);
        $manager->flush();
        $this->addReference("groupe-competition", $groupe);
        
        $groupe = new Groupe();
        $groupe->setNom("Loisirs");
        $groupe->setDescription("Cours Loisirs");
        $groupe->setCategorie(Groupe::LOISIR);
        $groupe->setOrder(1);
        $groupe->setMultiple(TRUE);
        $groupe->setCapacity(30);
        $groupe->addHoraire(new Horaire(4, 9, 10, "Cours 1"));
        $groupe->addHoraire(new Horaire(5, 20, 21.25,  "Cours 2"));
        
        $manager->persist($groupe);
        $manager->flush();
        $this->addReference("groupe-loisirs", $groupe);
    }

    /**
     * Order when loading the fixtures
     */
    public function getOrder() {
        return 2;
    }
}
