<?php

namespace App\Controller;

use App\Entity\TFTournament;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\VarDumper\VarDumper;

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
     * @Route("/tournament/create", name="chosen-type")
     */
    public function chooseTournament() {
        return $this->render('tournament/chosen-type.html.twig');
    }

    /**
     * @Route("/tournament/create/{type}", name="create-tournament", requirements={"\s"})
     */
    public function createTournament (Request $request, string $type) {

        $tournament = new TFTournament;
        $form = $this->createForm('App\Form\Type\TFTournamentType');

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            VarDumper::dump($form->isValid());
            VarDumper::dump($form); die;
        }

        return $this->render('tournament/new-tournament.html.twig', [
            'form' => $form->createView(),
            'type' => $type
        ]);
    }
}
