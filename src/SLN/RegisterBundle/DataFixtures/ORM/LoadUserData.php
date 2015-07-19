<?php

namespace SLN\RegisterBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use SLN\RegisterBundle\Entity\User;

class LoadUserData implements FixtureInterface
{
    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
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

        $manager->persist($user);
        $manager->flush();

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
    }
}
