<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{

    public function __construct(
        private UserPasswordHasherInterface $passwordHasher,
        )
    {}

    public function load(ObjectManager $manager)
    {
        $user = new User();
            $user->setEmail('test@mail.com');
            $user->setName('admin');
            $user->setRoles(['ROLE_ADMIN']);
            $user->setPassword(
                $this->passwordHasher->hashPassword(
                    $user,
                    'admin'
                )
            );

            $manager->persist($user);

        for ($i = 1; $i < 10; $i++) {
            $user = new User();
            $user->setEmail('test' . $i . '@mail.com');
            $user->setName('test ' . $i);
            $user->setRoles(['ROLE_USER']);
            $user->setPassword(
                $this->passwordHasher->hashPassword(
                    $user,
                    '12345678'
                )
            );

            $manager->persist($user);
        }

        $manager->flush();
    }
}