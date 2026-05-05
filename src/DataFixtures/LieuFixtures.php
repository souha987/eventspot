<?php

namespace App\DataFixtures;

use App\Entity\Lieu;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class LieuFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $lieux = [
            ['nom' => 'Centre de congrès', 'adresse' => '1 Place du Congrès', 'ville' => 'Paris', 'capacite' => 500],
            ['nom' => 'Salle polyvalente', 'adresse' => '12 Rue de la Mairie', 'ville' => 'Lyon', 'capacite' => 200],
            ['nom' => 'Amphithéâtre universitaire', 'adresse' => '5 Avenue de l\'Université', 'ville' => 'Bordeaux', 'capacite' => 300],
            ['nom' => 'Espace coworking', 'adresse' => '8 Rue des Startups', 'ville' => 'Nantes', 'capacite' => 80],
            ['nom' => 'Parc municipal', 'adresse' => '3 Allée des Fleurs', 'ville' => 'Marseille', 'capacite' => 1000],
        ];

        foreach ($lieux as $i => $data) {
            $lieu = new Lieu();
            $lieu->setNom($data['nom']);
            $lieu->setAdresse($data['adresse']);
            $lieu->setVille($data['ville']);
            $lieu->setCapacite($data['capacite']);
            $manager->persist($lieu);
            $this->addReference('lieu_' . $i, $lieu);
        }

        $manager->flush();
    }
}