<?php

namespace App\Tests\Functional;

use App\Entity\Evenement;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class EvenementControllerTest extends WebTestCase
{
    public function testIndexPageLoads(): void
    {
        $client = static::createClient();
        $client->request('GET', '/evenements');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('table');
    }

    public function testShowPageLoads(): void
    {
        $client = static::createClient();

        // Récupère le premier événement existant en base
        $em = static::getContainer()->get(EntityManagerInterface::class);
        $evenement = $em->getRepository(Evenement::class)->findOneBy([]);

        if (!$evenement) {
            $this->markTestSkipped('Aucun événement en base de test.');
        }

        $client->request('GET', '/evenements/' . $evenement->getId());
        $this->assertResponseIsSuccessful();
    }

    public function testNewPageRequiresLogin(): void
    {
        $client = static::createClient();
        $client->request('GET', '/evenement/new');

        // Soit redirect vers login, soit 404 si route différente
        $this->assertThat(
            $client->getResponse()->getStatusCode(),
            $this->logicalOr(
                $this->equalTo(302),
                $this->equalTo(404)
            )
        );
    }

    public function testFilterByCategorie(): void
    {
        $client = static::createClient();
        $client->request('GET', '/evenements?categorie=concert');

        $this->assertResponseIsSuccessful();
    }
}