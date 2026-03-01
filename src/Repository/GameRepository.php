<?php

namespace App\Repository;

use App\Entity\Game;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<Game>
 */
class GameRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Game::class);
    }

    /**
     * Recherche par titre ou description
     * @return Game[]
     */
    public function searchByQuery(string $q): array
    {
        return $this->createQueryBuilder('g')
            ->andWhere('LOWER(g.title) LIKE :q OR LOWER(g.description) LIKE :q')
            ->setParameter('q', '%'.mb_strtolower(trim($q)).'%')
            ->orderBy('g.id', 'DESC')
            ->getQuery()
            ->getResult();
    }
}