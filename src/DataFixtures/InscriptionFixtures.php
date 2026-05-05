<?php

namespace App\DataFixtures;

use App\Entity\Inscription;
use App\Entity\Evenement;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class InscriptionFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker   = Factory::create('fr_FR');
        $statuts = ['confirmee', 'en_attente', 'annulee'];
        $count   = 0;

        for ($i = 0; $i < 15 && $count < 30; $i++) {
            $evenement = $this->getReference('event_' . $i, Evenement::class);
            $max = min(2, $evenement->getCapaciteMax());

            for ($j = 0; $j < $max && $count < 30; $j++) {
                $insc = new Inscription();
                $insc->setEvenement($evenement);
                $insc->setParticipant($this->getReference('user_part_' . $faker->numberBetween(0, 4), User::class));
                $insc->setStatut($faker->randomElement($statuts));
                $insc->setCommentaire($faker->optional()->sentence());
                $manager->persist($insc);
                $count++;
            }
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [EvenementFixtures::class, UserFixtures::class];
    }
}