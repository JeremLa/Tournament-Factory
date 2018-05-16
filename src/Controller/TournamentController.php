<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class TournamentController extends Controller
{
    /**
     * @Route("/tournament", name="my_tournament")
     */
    public function index()
    {
        return $this->render('tournament/index.html.twig', [
            'controller_name' => 'TournamentController',
        ]);
    }

    /**
     * @Route("/tournament/new", name="add_tournament")
     */
    public function newTournament() {
        return $this->render('tournament/new.html.twig');
    }
}
