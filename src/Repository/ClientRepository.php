<?php

namespace App\Repository;

use App\Entity\Client;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Client>
 */
class ClientRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Client::class);
    }

    /**
     * @return Client[]
     */
    public function search(?string $search): array
    {
        $qb = $this->createQueryBuilder('c')
            ->orderBy('c.name', 'ASC');

        if ($search) {
            $qb->andWhere('c.name LIKE :search OR c.nif LIKE :search OR c.nis LIKE :search OR c.ai LIKE :search')
                ->setParameter('search', '%'.$search.'%');
        }

        return $qb->getQuery()->getResult();
    }
}
