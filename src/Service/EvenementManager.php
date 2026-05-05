<?php

namespace App\Service;

use App\Entity\Evenement;
use App\Entity\Inscription;
use App\Entity\User;
use App\Repository\EvenementRepository;
use Doctrine\ORM\EntityManagerInterface;

class EvenementManager
{
    public function __construct(
        private EntityManagerInterface $em,
        private EvenementRepository $evenementRepository,
    ) {}

    /**
     * Vérifie si un événement est complet
     */
    public function isComplet(Evenement $evenement): bool
    {
        return $evenement->getInscriptions()->count() >= $evenement->getCapaciteMax();
    }

    /**
     * Calcule le taux de remplissage en %
     */
    public function getTauxRemplissage(Evenement $evenement): int
    {
        if ($evenement->getCapaciteMax() === 0) return 0;

        return (int) round(
            $evenement->getInscriptions()->count() / $evenement->getCapaciteMax() * 100
        );
    }

    /**
     * Inscrit un utilisateur à un événement
     */
    public function inscrire(Evenement $evenement, User $user): Inscription
    {
        if ($this->isComplet($evenement)) {
            throw new \RuntimeException('Cet événement est complet.');
        }

        $inscription = new Inscription();
        $inscription->setEvenement($evenement);
        $inscription->setParticipant($user);
        $inscription->setStatut('en_attente');
        $inscription->setDateInscription(new \DateTimeImmutable());

        $this->em->persist($inscription);

        // Met à jour le statut si complet après inscription
        if ($this->isComplet($evenement)) {
            $evenement->setStatut('complet');
        }

        $this->em->flush();

        return $inscription;
    }

    /**
     * Retourne les événements à venir
     */
    public function getEvenementsAVenir(): array
    {
        return $this->evenementRepository->createQueryBuilder('e')
            ->where('e.dateDebut > :now')
            ->setParameter('now', new \DateTime())
            ->orderBy('e.dateDebut', 'ASC')
            ->getQuery()
            ->getResult();
    }
}