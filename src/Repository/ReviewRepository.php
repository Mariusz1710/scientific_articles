<?php

namespace App\Repository;

use App\Entity\Review;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Review>
 */
class ReviewRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Review::class);
    }

    /** @return Review[] */
    public function findForReviewer(User $reviewer): array
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.reviewer = :reviewer')
            ->setParameter('reviewer', $reviewer)
            ->orderBy('r.assignedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
