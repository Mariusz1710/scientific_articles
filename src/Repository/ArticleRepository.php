<?php

namespace App\Repository;

use App\Entity\Article;
use App\Enum\ArticleStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Article>
 */
class ArticleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Article::class);
    }

    /**
     * Filtering used by the search page: title, authors, keywords, status, category.
     *
     * @param array{title?: string, authors?: string, keywords?: string, status?: string, category?: int} $criteria
     *
     * @return Article[]
     */
    public function search(array $criteria): array
    {
        return $this->buildSearchQuery($criteria)
            ->orderBy('a.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param array{title?: string, authors?: string, keywords?: string, status?: string, category?: int} $criteria
     */
    public function buildSearchQuery(array $criteria): QueryBuilder
    {
        $qb = $this->createQueryBuilder('a')
            ->join('a.category', 'c')->addSelect('c')
            ->join('a.submitter', 's')->addSelect('s');

        if (!empty($criteria['title'])) {
            $qb->andWhere('LOWER(a.title) LIKE :title')
                ->setParameter('title', '%'.mb_strtolower($criteria['title']).'%');
        }

        if (!empty($criteria['authors'])) {
            $qb->andWhere('LOWER(a.authors) LIKE :authors')
                ->setParameter('authors', '%'.mb_strtolower($criteria['authors']).'%');
        }

        if (!empty($criteria['keywords'])) {
            $qb->andWhere('LOWER(a.keywords) LIKE :keywords')
                ->setParameter('keywords', '%'.mb_strtolower($criteria['keywords']).'%');
        }

        if (!empty($criteria['status'])) {
            $qb->andWhere('a.status = :status')
                ->setParameter('status', ArticleStatus::from($criteria['status']));
        }

        if (!empty($criteria['category'])) {
            $qb->andWhere('c.id = :category')
                ->setParameter('category', $criteria['category']);
        }

        return $qb;
    }

    /** @return Article[] */
    public function findPublished(): array
    {
        return $this->findBy(['status' => ArticleStatus::PUBLISHED], ['publishedAt' => 'DESC']);
    }

    /** @return Article[] */
    public function findAwaitingEditorDecision(): array
    {
        return $this->findBy(['status' => ArticleStatus::ACCEPTED], ['updatedAt' => 'ASC']);
    }

    /**
     * @return array<string, int> counts per status, used by the stats dashboard
     */
    public function countByStatus(): array
    {
        $rows = $this->createQueryBuilder('a')
            ->select('a.status AS status, COUNT(a.id) AS total')
            ->groupBy('a.status')
            ->getQuery()
            ->getResult();

        $counts = [];
        foreach ($rows as $row) {
            $status = $row['status'] instanceof ArticleStatus ? $row['status']->value : $row['status'];
            $counts[$status] = (int) $row['total'];
        }

        foreach (ArticleStatus::cases() as $case) {
            $counts[$case->value] ??= 0;
        }

        return $counts;
    }
}
