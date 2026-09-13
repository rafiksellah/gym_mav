<?php

namespace App\Repository;

use App\Entity\Invoice;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Invoice>
 */
class InvoiceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Invoice::class);
    }

    /**
     * @return Invoice[]
     */
    public function search(?string $search): array
    {
        $qb = $this->createQueryBuilder('i')
            ->join('i.client', 'c')->addSelect('c')
            ->orderBy('i.invoiceDate', 'DESC')
            ->addOrderBy('i.id', 'DESC');

        if ($search) {
            $qb->andWhere('i.number LIKE :search OR c.name LIKE :search')
                ->setParameter('search', '%'.$search.'%');
        }

        return $qb->getQuery()->getResult();
    }
}
