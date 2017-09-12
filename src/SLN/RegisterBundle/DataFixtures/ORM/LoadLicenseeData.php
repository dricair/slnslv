<?php
/**
  * Sample data for tests: Licensee data 
  *
  * @author Cédric Airaud
  */

namespace SLN\RegisterBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use SLN\RegisterBundle\Entity\Licensee;
use SLN\RegisterBundle\Entity\Saison;
use SLN\RegisterBundle\Entity\LicenseeSaison;


/**
 * Load licensee data to database
 */
class LoadLicenseeData extends AbstractFixture implements OrderedFixtureInterface
{
    private function addSaisonToLicensee(ObjectManager $manager, Licensee $licensee, Saison $saison) {
        $saison_link = new LicenseeSaison();
        $saison_link->setLicensee($licensee);
        $saison_link->setSaison($saison);
        $licensee->addSaisonLink($saison_link);

        return $saison_link;
    }

    /**
     * Load Licensee class data
     *
     * @param ObjectManager $manager Manager instance for load.
     */
    public function load(ObjectManager $manager)
    {
        // Normal user has three licensee, one for each Groupe
        // id=1
        $licensee = new Licensee();
        $licensee->setNom("Nom1");
        $licensee->setPrenom("Prenom1");
        $licensee->setSexe(Licensee::HOMME);
        $licensee->setNaissance(new \Datetime("2001-01-01"));
        $licensee->setIuf("11111111");
        $licensee->setUser($this->getReference('user-standard'));
        $manager->persist($licensee);

        $saison_link = $this->addSaisonToLicensee($manager, $licensee, $this->getReference("saison-current"));
        $saison_link->setGroupe($this->getReference('groupe-ecole'));
        $saison_link->setNewGroupe($this->getReference('groupe-competition'));
        $manager->persist($saison_link);

        $manager->flush();

        // id=2
        $licensee = new Licensee();
        $licensee->setNom("Nom2");
        $licensee->setPrenom("Prenom2");
        $licensee->setSexe(Licensee::FEMME);
        $licensee->setNaissance(new \Datetime("2002-07-25"));
        $licensee->setIuf("22222222");
        $licensee->setUser($this->getReference('user-standard'));
        $licensee->setAutorisationPhotos(False);
        $manager->persist($licensee);

        $saison_link = $this->addSaisonToLicensee($manager, $licensee, $this->getReference("saison-current"));
        $saison_link->setGroupe($this->getReference('groupe-competition'));
        $saison_link->setNewGroupe($this->getReference('groupe-loisirs'));
        $manager->persist($saison_link);

        $manager->flush();

        // id=3
        $licensee = new Licensee();
        $licensee->setNom("Nom3");
        $licensee->setPrenom("Prenom3");
        $licensee->setSexe(Licensee::FEMME);
        $licensee->setNaissance(new \Datetime("2003-12-31"));
        $licensee->setIuf("33333333");
        $licensee->setUser($this->getReference('user-standard'));
        $manager->persist($licensee);

        $saison_link = $this->addSaisonToLicensee($manager, $licensee, $this->getReference("saison-current"));
        $saison_link->setGroupe($this->getReference('groupe-loisirs'));
        $manager->persist($saison_link);

        $manager->flush();


        // Admin user has one Officiel/Loisirs + 1 Bureau with no Groupe
        // id=4
        $licensee = new Licensee();
        $licensee->setNom("Nom4");
        $licensee->setPrenom("Prenom4");
        $licensee->setSexe(Licensee::HOMME);
        $licensee->setNaissance(new \Datetime("1980-03-31"));
        $licensee->setIuf("44444444");
        $licensee->setUser($this->getReference('user-admin'));
        $licensee->setFonctions(array(Licensee::OFFICIEL));
        $manager->persist($licensee);

        $saison_link = $this->addSaisonToLicensee($manager, $licensee, $this->getReference("saison-current"));
        $saison_link->setGroupe($this->getReference('groupe-loisirs'));
        $manager->persist($saison_link);

        $manager->flush();

        // id=5
        $licensee = new Licensee();
        $licensee->setNom("Nom5");
        $licensee->setPrenom("Prenom5");
        $licensee->setSexe(Licensee::FEMME);
        $licensee->setNaissance(new \Datetime("1980-04-04"));
        $licensee->setIuf("");
        $licensee->setUser($this->getReference('user-admin'));
        $licensee->setFonctions(array(Licensee::BUREAU));

        $manager->persist($licensee);
        $manager->flush();
    }

    /**
     * Order when loading the fixtures
     */
    public function getOrder() {
        return 4;
    }
}
    

