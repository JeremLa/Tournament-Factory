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

    public function findUsersByArrayId(array $arrayId)
    {
        return $this->createQueryBuilder('TFU')
                ->where('TFU.id IN (:array)')
                ->setParameter('array',$arrayId)
                ->getQuery()
                ->getResult();
    }
}
