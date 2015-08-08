<?php
/**
  * Sample data for tests: User data 
  *
  * @author Cédric Airaud
  */

namespace SLN\RegisterBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use SLN\RegisterBundle\Entity\User;


/**
 * Load user data to database
 */
class LoadUserData extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * Load User class data
     *
     * @param ObjectManager $manager Manager instance for load.
     *
     * @return void
     */
    public function load(ObjectManager $manager)
    {
        // ID=1 -> admin
        $user = new User();
        $user->setTitre(User::MR);
        $user->setNom("Test");
        $user->setPrenom("Admin");
        $user->setAdresse("Adresse");
        $user->setCodePostal("06700");
        $user->setVille("St Laurent du Var");
        $user->setTelPortable("0601234567");
        $user->setUsername("test-admin");
        $user->setEmail("test-admin@test.com");
        $user->setPlainPassword("test");
        $user->setEnabled(true);
        $user->addRole("ROLE_ADMIN");

        $manager->persist($user);
        $manager->flush();
        $this->addReference("user-admin", $user);

        // ID=2 -> User with no admin rights
        $user = new User();
        $user->setTitre(User::MME);
        $user->setNom("Test");
        $user->setPrenom("User");
        $user->setAdresse("Adresse");
        $user->setCodePostal("06700");
        $user->setVille("St Laurent du Var");
        $user->setTelPortable("0601234567");
        $user->setUsername("test-user");
        $user->setEmail("test-user@test.com");
        $user->setPlainPassword("test");
        $user->setEnabled(true);

        $manager->persist($user);
        $manager->flush();
        $this->addReference("user-standard", $user);
    }

    /**
     * Order when loading the fixtures
     */
    public function getOrder() {
        return 1;
    }
}
