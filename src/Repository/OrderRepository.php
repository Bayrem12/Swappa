<?php

namespace App\Repository;

use App\Entity\Order;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Order>
 *
 * @method Order|null find($id, $lockMode = null, $lockVersion = null)
 * @method Order|null findOneBy(array $criteria, array $orderBy = null)
 * @method Order[]    findAll()
 * @method Order[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OrderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Order::class);
    }

//    /**
//     * @return Order[] Returns an array of Order objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('o')
//            ->andWhere('o.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('o.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Order
//    {
//        return $this->createQueryBuilder('o')
//            ->andWhere('o.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
public function countLastWeekOrders(): int
{
    return $this->createQueryBuilder('o')
        ->select('COUNT(o.id)')
        ->where('o.created_at >= :date')
        ->setParameter('date', new \DateTime('-7 days'))
        ->getQuery()
        ->getSingleScalarResult();
}

public function getMonthlyRevenue(): float
{
    return $this->createQueryBuilder('o')
        ->select('SUM(o.total)')
        ->where('o.created_at >= :date')
        ->setParameter('date', new \DateTime('first day of this month'))
        ->getQuery()
        ->getSingleScalarResult() ?? 0;
}
public function getUserWithMostOrders(): array
{
    return $this->createQueryBuilder('o')
        ->select('u.id, u.email, COUNT(o.id) as orderCount')
        ->join('o.user', 'u')
        ->groupBy('u.id')
        ->orderBy('orderCount', 'DESC')
        ->setMaxResults(1)
        ->getQuery()
        ->getOneOrNullResult();
}
 public function countByStatus(string $status): int
{
    return $this->createQueryBuilder('o')
        ->select('COUNT(o.id)')
        ->where('o.status = :status')
        ->setParameter('status', $status)
        ->getQuery()
        ->getSingleScalarResult();
}

public function getCompletedRevenueThisMonth(): float
{
    return $this->createQueryBuilder('o')
        ->select('SUM(o.total)')
        ->where('o.status = :status')
        ->andWhere('o.created_at >= :date')
        ->setParameter('status', 'completed')
        ->setParameter('date', new \DateTime('first day of this month'))
        ->getQuery()
        ->getSingleScalarResult() ?? 0;
}

}
