<?php

namespace App\Controller;

use App\Entity\TFTournament;
use App\Services\Enum\TournamentTypeEnum;
use App\Entity\TFUser;
use App\Repository\TFTournamentRepository;
use App\Repository\TFUserRepository;
use App\Services\TournamentService;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class TournamentController extends Controller
{
    /* @var EntityManagerInterface $entityManager */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/tournament", name="my_tournament")
     */
    public function index()
    {
        /**
         * @var TFTournament[] $tournaments
         */
        $tournaments = $this->entityManager->getRepository('App\Entity\TFTournament')->findAll();

        return $this->render('tournament/index.html.twig', [
            'controller_name' => 'TournamentController',
            'tournaments' => $tournaments
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
        if(!TournamentTypeEnum::getTypeName($type)){
            throw new NotFoundHttpException('Ce type de tournoi n\'existe pas');
        }

        $tournament = new TFTournament($type);
        $form = $this->createForm('App\Form\Type\TFTournamentType', $tournament);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $tournament->setOwner($this->getUser()->getTFUser());

            $this->entityManager->persist($tournament);
            $this->entityManager->flush();

            $this->addFlash('success', 'tournament.new.message');

            return $this->redirectToRoute('chosen-type');
        }

        return $this->render('tournament/new-tournament.html.twig', [
            'form' => $form->createView(),
            'type' => $type
        ]);
    }

    /**
     * @Route("/tournament/remove", name="remove_tournament")
     */
    public function removeTournament (Request $request) {
        $index = $request->get('tournament-id');
        $tournament = $this->entityManager->getRepository('App:TFTournament')->find($index);

        if($tournament) {
            $this->entityManager->remove($tournament);
            $this->entityManager->flush();
            $this->addFlash('success', 'tournament.remove.message');
            return $this->redirectToRoute('my_tournament');
        }

        $this->addFlash('warning', 'tournament.remove.message');

        return $this->redirectToRoute('my_tournament');
    }

    /**
     * @Route("/tournament/{tournamentId}/addParticipant", name="add-participant", requirements={"\s"})
     */
    public function addParticipant (Request $request, string $tournamentId, TournamentService $tournamentService)
    {
        /* @var TFTournamentRepository $repo */
        $repo = $this->entityManager->getRepository(TFTournament::class);
        $tournament = $repo->find($tournamentId);

        if($tournament == null){
            throw new NotFoundHttpException("Ce tournoi n'existe pas");
        }

        /* @var TFUser[] $players_in_tournament */
        $tournamentPlayers = $tournament->getPlayers()->toArray();

        /* @var  TFUserRepository $repo */
        $repo = $this->entityManager->getRepository(TFUser::class);
        /* @var Collection $users */
        $users = $repo->findAll();

        $form = $this->createForm('App\Form\Type\AddParticipantToTournamentType', $tournament, ['users' => $users, 'players' => $tournamentPlayers]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()){
            /* @var array $submited */
            $submited = $request->get('add_participant_to_tournament');
            $tournamentService->updateTournamentParticipant($submited, $tournament);

            $this->addFlash('success', 'tournament.update.participant');

            return $this->redirectToRoute('my_tournament');
        }

        return $this->render('tournament/add-participant.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
