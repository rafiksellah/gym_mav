<?php

namespace App\Repository;

use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Product>
 */
class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    private function activeQueryBuilder(): QueryBuilder
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.isActive = true');
    }

    public function createFilteredQueryBuilder(?string $categorySlug, ?string $search): QueryBuilder
    {
        $qb = $this->activeQueryBuilder()
            ->join('p.category', 'c')
            ->orderBy('p.createdAt', 'DESC');

        if ($categorySlug) {
            $qb->andWhere('c.slug = :categorySlug')
                ->setParameter('categorySlug', $categorySlug);
        }

        if ($search) {
            $qb->andWhere('p.name LIKE :search OR p.shortDescription LIKE :search')
                ->setParameter('search', '%'.$search.'%');
        }

        return $qb;
    }

    public function paginate(QueryBuilder $qb, int $page, int $limit): Paginator
    {
        $qb->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        return new Paginator($qb);
    }

    /**
     * @return Product[]
     */
    public function findFeatured(int $limit = 6): array
    {
        return $this->activeQueryBuilder()
            ->andWhere('p.isFeatured = true')
            ->orderBy('p.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function findActiveBySlug(string $slug): ?Product
    {
        return $this->activeQueryBuilder()
            ->andWhere('p.slug = :slug')
            ->setParameter('slug', $slug)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function countActive(): int
    {
        return (int) $this->activeQueryBuilder()
            ->select('COUNT(p.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }
}
