<?php
/**
  * Sample data for tests: Saison data 
  *
  * @author Cédric Airaud
  */

namespace SLN\RegisterBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use SLN\RegisterBundle\Entity\Saison;

/**
 * Load saison data to database
 */
class LoadSaisonData extends AbstractFixture implements OrderedFixtureInterface
{
    /* Load saison class data 
     *
     @param ObjectManager $manager Manager instance for load.
     *
     * @return void
     */
    public function load(ObjectManager $manager)
    {
        // Id=1
        $date = new \DateTime();
        $date->modify("-1 year");
        $saison = new Saison();
        $saison->setNom("Saison Old");
        $saison->setStart($date);
        $saison->setActivated(FALSE);
        
        $manager->persist($saison);
        $manager->flush();
        $this->addReference("saison-old", $saison);

        // Id=2
        $date = new \DateTime();
        $date->modify("-6 months");
        $saison = new Saison();
        $saison->setNom("Saison Current");
        $saison->setStart($date);
        $saison->setActivated(FALSE);

        $manager->persist($saison);
        $manager->flush();
        $this->addReference("saison-current", $saison);

        // Id=3
        $date = new \DateTime();
        $date->modify("+6 months");
        $saison = new Saison();
        $saison->setNom("Saison Next");
        $saison->setStart($date);
        $saison->setActivated(TRUE);

        $manager->persist($saison);
        $manager->flush();
        $this->addReference("saison-next", $saison);
    }

    /**
     * Order when loading the fixtures
     */
    public function getOrder() {
        return 3;
    }
}

