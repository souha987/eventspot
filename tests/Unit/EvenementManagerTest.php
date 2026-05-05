<?php

namespace App\Tests\Unit;

use App\Entity\Evenement;
use App\Entity\Inscription;
use App\Entity\User;
use App\Repository\EvenementRepository;
use App\Service\EvenementManager;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class EvenementManagerTest extends TestCase
{
    private EvenementManager $manager;
    private EntityManagerInterface $em;
    private EvenementRepository $repo;

    protected function setUp(): void
    {
        $this->em   = $this->createMock(EntityManagerInterface::class);
        $this->repo = $this->createMock(EvenementRepository::class);
        $this->manager = new EvenementManager($this->em, $this->repo);
    }

    public function testInscrireCreatesInscription(): void
    {
        $evenement = new Evenement();
        $evenement->setTitre('Test Event');
        $evenement->setCapaciteMax(10);
        $evenement->setStatut('publie');

        $user = new User();
        $user->setEmail('test@example.com');

        $this->em->expects($this->once())->method('persist');
        $this->em->expects($this->once())->method('flush');

        $inscription = $this->manager->inscrire($evenement, $user);

        $this->assertInstanceOf(Inscription::class, $inscription);
        $this->assertSame($evenement, $inscription->getEvenement());
        $this->assertSame($user, $inscription->getParticipant());
    }

    public function testIsCompletReturnsFalseWhenPlacesAvailable(): void
    {
        $evenement = new Evenement();
        $evenement->setCapaciteMax(10);

        $this->assertFalse($this->manager->isComplet($evenement));
    }

    public function testEvenementCompletThrowsException(): void
    {
        $evenement = new Evenement();
        $evenement->setCapaciteMax(0);

        $user = new User();

        $this->expectException(\RuntimeException::class);

        $this->manager->inscrire($evenement, $user);
    }

    public function testGetTauxRemplissage(): void
    {
        $evenement = new Evenement();
        $evenement->setCapaciteMax(0);

        $this->assertSame(0, $this->manager->getTauxRemplissage($evenement));
    }
}