<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    public function __construct(
        private UserPasswordHasherInterface $hasher
    ) {}

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        $admin = new User();
        $admin->setEmail('admin@eventspot.com');
        $admin->setPseudo('Admin');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setPassword($this->hasher->hashPassword($admin, 'admin123'));
        $manager->persist($admin);
        $this->addReference('user_admin', $admin);

        foreach ([1, 2] as $i) {
            $orga = new User();
            $orga->setEmail('orga' . $i . '@eventspot.com');
            $orga->setPseudo('Organisateur' . $i);
            $orga->setRoles(['ROLE_ORGANISATEUR']);
            $orga->setPassword($this->hasher->hashPassword($orga, 'orga123'));
            $manager->persist($orga);
            $this->addReference('user_orga_' . $i, $orga);
        }

        for ($i = 0; $i < 5; $i++) {
            $user = new User();
            $user->setEmail($faker->unique()->safeEmail());
            $user->setPseudo($faker->userName());
            $user->setRoles(['ROLE_USER']);
            $user->setPassword($this->hasher->hashPassword($user, 'user123'));
            $manager->persist($user);
            $this->addReference('user_part_' . $i, $user);
        }

        $manager->flush();
    }
}