<?php

namespace App\Repository;

use App\Entity\Produit;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder;

class ProduitRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Produit::class);
    }

    /**
     * Find products by name (case-insensitive).
     *
     * @param string $name
     * @return Produit[]
     */
    public function findByName(string $name)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('LOWER(p.name) LIKE LOWER(:name)')
            ->setParameter('name', '%' . $name . '%')
            ->orderBy('p.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find products with a minimum quantity.
     *
     * @param int $minQuantity
     * @return Produit[]
     */
    public function findByMinQuantity(int $minQuantity)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.quantity >= :minQuantity')
            ->setParameter('minQuantity', $minQuantity)
            ->orderBy('p.quantity', 'DESC')
            ->getQuery()
            ->getResult();
    }
    
}
