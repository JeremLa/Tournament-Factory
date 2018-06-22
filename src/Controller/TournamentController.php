<?php

namespace App\Controller;

use App\Entity\TFTournament;
use App\Entity\TFUser;
use App\Entity\User;
use App\Repository\TFTournamentRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\VarDumper\VarDumper;

class TournamentController extends Controller
{
    /**
     * @var EntityManager $entityManger
     */
    private $entityManger;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManger = $entityManager;
    }

    /**
     * @Route("/tournament", name="my_tournament")
     */
    public function index()
    {
        /**
         * @var TFTournament $tournaments
         */
        $tournaments = $this->entityManger->getRepository('App\Entity\TFTournament')->findAll();

        return $this->render('tournament/index.html.twig', [
            'controller_name' => 'TournamentController',
            'tournaments' => $tournaments
        ]);
    }

    /**
     * @Route("/tournament/new", name="add_tournament")
     */
    public function newTournament() {
        return $this->render('tournament/new.html.twig');
    }
}
