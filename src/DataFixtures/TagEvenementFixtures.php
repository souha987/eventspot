<?php

namespace App\DataFixtures;

use App\Entity\TagEvenement;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class TagEvenementFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $tags = [
            ['nom' => 'Networking', 'couleur' => '#3498db'],
            ['nom' => 'Tech',       'couleur' => '#9b59b6'],
            ['nom' => 'Gratuit',    'couleur' => '#2ecc71'],
            ['nom' => 'Startup',    'couleur' => '#e67e22'],
            ['nom' => 'Formation',  'couleur' => '#1abc9c'],
            ['nom' => 'Culture',    'couleur' => '#e91e63'],
            ['nom' => 'Sport',      'couleur' => '#f39c12'],
            ['nom' => 'Famille',    'couleur' => '#27ae60'],
        ];

        foreach ($tags as $i => $data) {
            $tag = new TagEvenement();
            $tag->setNom($data['nom']);
            $tag->setCouleur($data['couleur']);
            $manager->persist($tag);
            $this->addReference('tag_' . $i, $tag);
        }

        $manager->flush();
    }
}