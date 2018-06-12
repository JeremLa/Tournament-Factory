<?php

namespace App\Repository;

use App\Entity\TFUser;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method TFUser|null find($id, $lockMode = null, $lockVersion = null)
 * @method TFUser|null findOneBy(array $criteria, array $orderBy = null)
 * @method TFUser[]    findAll()
 * @method TFUser[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TFUserRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, TFUser::class);
    }

//    /**
//     * @return TFUser[] Returns an array of TFUser objects
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
    public function findOneBySomeField($value): ?TFUser
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
