<?php

namespace App\Repository;

use App\Entity\TFMatch;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method TFMatch|null find($id, $lockMode = null, $lockVersion = null)
 * @method TFMatch|null findOneBy(array $criteria, array $orderBy = null)
 * @method TFMatch[]    findAll()
 * @method TFMatch[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TFMatchRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, TFMatch::class);
    }

//    /**
//     * @return TFMatch[] Returns an array of TFMatch objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('t.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?TFMatch
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
