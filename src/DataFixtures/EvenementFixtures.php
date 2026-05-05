<?php

namespace App\DataFixtures;

use App\Entity\Evenement;
use App\Entity\Lieu;
use App\Entity\TagEvenement;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class EvenementFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        $categories = ['conference', 'atelier', 'meetup', 'formation', 'concert'];
        $statuts    = ['brouillon', 'publie', 'complet', 'annule'];

        $titres = [
            'Conférence sur l\'IA et le futur du travail',
            'Atelier PHP avancé avec Symfony',
            'Meetup des entrepreneurs locaux',
            'Formation DevOps & CI/CD',
            'Concert acoustique au parc',
            'Hackathon Green Tech 2026',
            'Journée portes ouvertes startup',
            'Atelier aquarelle pour débutants',
            'Forum emploi numérique',
            'Conférence cybersécurité',
            'Meetup React & Next.js',
            'Formation gestion de projet Agile',
            'Festival de musique urbaine',
            'Atelier cuisine du monde',
            'Séminaire leadership et management',
        ];

        for ($i = 0; $i < 15; $i++) {
            $e = new Evenement();
            $e->setTitre($titres[$i]);
            $e->setDescription($faker->paragraphs(2, true));
            $e->setDateDebut($faker->dateTimeBetween('-1 month', '+3 months'));
            $e->setDateFin($faker->dateTimeBetween('+3 months', '+4 months'));
            $e->setCapaciteMax($faker->numberBetween(20, 200));
            $e->setPrix($faker->randomElement([0, 15.0, 25.0, 50.0, 99.0]));
            $e->setCategorie($faker->randomElement($categories));
            $e->setStatut($faker->randomElement($statuts));
            $e->setLieu($this->getReference('lieu_' . $faker->numberBetween(0, 4), Lieu::class));
            $e->setOrganisateur($this->getReference('user_orga_' . $faker->numberBetween(1, 2), User::class));

            $tagIndexes = $faker->randomElements(range(0, 7), $faker->numberBetween(1, 4));
            foreach ($tagIndexes as $ti) {
                $e->addTag($this->getReference('tag_' . $ti, TagEvenement::class));
            }

            $manager->persist($e);
            $this->addReference('event_' . $i, $e);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [LieuFixtures::class, UserFixtures::class, TagEvenementFixtures::class];
    }
}