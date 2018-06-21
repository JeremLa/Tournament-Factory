<?php
/**
 * Created by PhpStorm.
 * User: AHermes
 * Date: 21/06/2018
 * Time: 13:58
 */

namespace App\Repository;


use App\Entity\TFTournament;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method TFTournament|null find($id, $lockMode = null, $lockVersion = null)
 * @method TFTournament|null findOneBy(array $criteria, array $orderBy = null)
 * @method TFTournament[]    findAll()
 * @method TFTournament[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TFTournamentRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, TFTournament::class);
    }
}