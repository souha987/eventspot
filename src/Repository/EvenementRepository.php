<?php
namespace App\Repository;

use App\Entity\Evenement;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;

class EvenementRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Evenement::class);
    }

    public function findUpcoming(int $limit = 6): array
    {
        return $this->createQueryBuilder('e')
            ->where('e.dateDebut >= :now')
            ->andWhere('e.statut = :statut')
            ->setParameter('now', new \DateTime())
            ->setParameter('statut', 'publie')
            ->orderBy('e.dateDebut', 'ASC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function findByFiltersQuery(
        ?string $categorie = null,
        ?string $ville = null,
        ?string $q = null,
        ?string $tag = null,
    ): Query {
        $qb = $this->createQueryBuilder('e')
            ->leftJoin('e.lieu', 'l')
            ->leftJoin('e.tags', 't')
            ->addSelect('l');

        if ($categorie) {
            $qb->andWhere('e.categorie = :categorie')
               ->setParameter('categorie', $categorie);
        }

        if ($ville) {
            $qb->andWhere('l.ville LIKE :ville')
               ->setParameter('ville', '%' . $ville . '%');
        }

        if ($q) {
            $qb->andWhere('e.titre LIKE :q OR e.description LIKE :q')
               ->setParameter('q', '%' . $q . '%');
        }

        if ($tag) {
            $qb->andWhere('t.nom = :tag')
               ->setParameter('tag', $tag);
        }

        return $qb->orderBy('e.dateDebut', 'ASC')
                  ->getQuery();
    }

    public function findByFilters(
        ?string $categorie = null,
        ?string $ville = null,
        ?string $q = null,
        ?float $prixMax = null,
    ): array {
        $qb = $this->createQueryBuilder('e')
            ->leftJoin('e.lieu', 'l')
            ->addSelect('l');

        if ($categorie) {
            $qb->andWhere('e.categorie = :categorie')
               ->setParameter('categorie', $categorie);
        }

        if ($ville) {
            $qb->andWhere('l.ville LIKE :ville')
               ->setParameter('ville', '%' . $ville . '%');
        }

        if ($q) {
            $qb->andWhere('e.titre LIKE :q OR e.description LIKE :q')
               ->setParameter('q', '%' . $q . '%');
        }

        if ($prixMax !== null) {
            $qb->andWhere('e.prix <= :prixMax OR e.prix IS NULL')
               ->setParameter('prixMax', $prixMax);
        }

        return $qb->orderBy('e.dateDebut', 'ASC')
                  ->getQuery()
                  ->getResult();
    }
}