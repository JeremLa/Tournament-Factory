<?php

namespace App\Controller;

use App\Entity\TFTournament;
use App\Form\Type\ManageParticipantType;
use App\Form\Type\ScoreType;
use App\Form\Type\TFTournamentType;
use App\Services\Enum\TournamentStatusEnum;
use App\Services\Enum\TournamentTypeEnum;
use App\Services\MatchService;
use App\Services\TournamentRulesServices;
use App\Services\TournamentService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;


class TournamentController extends Controller
{
    /* @var EntityManagerInterface $entityManager */
    private $entityManager;

    /* @var TournamentRulesServices $ruleServices */
    private $ruleServices;

    /* @var  TournamentService $tournamentService */
    private $tournamentService;

    /* @var MatchService $matchService */
    private $matchService;

    private const KEY_REFERER = 'referer';

    public function __construct(EntityManagerInterface $entityManager, TournamentRulesServices $rulesServices, TournamentService $tournamentService, MatchService $matchService)
    {
        $this->entityManager = $entityManager;
        $this->ruleServices = $rulesServices;
        $this->tournamentService = $tournamentService;
        $this->matchService = $matchService;
    }

    /**
     * @Route("/tournament", name="my_tournament")
     */
    public function index()
    {
        /* @var TFTournament[] $tournaments */
        $tournaments = $this->entityManager->getRepository('App\Entity\TFTournament')->getMyTournaments($this->getUser()->getTFUser());

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
            'type' => $type,
        ]);
    }

    /**
     * @Route("/tournament/remove", name="remove_tournament")
     */
    public function removeTournament (Request $request)
    {
        $index = $request->get('tournament-id');
        $redirectRoute = 'my_tournament';
        $tournament = $this->entityManager->getRepository('App:TFTournament')->find($index);


        if($tournament){
            if(!$this->ruleServices->canBeDeleted($tournament)) {
                return $this->redirectToRoute($redirectRoute);
            }

            $this->entityManager->remove($tournament);
            $this->entityManager->flush();
            $this->addFlash('success', 'tournament.remove.message');
            return $this->redirectToRoute($redirectRoute);
        }

        $this->addFlash('warning', 'tournament.remove.message');

        return $this->redirectToRoute($redirectRoute);
    }

    /**
     * @param Request $request
     * @param string $tournamentId
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     *
     * @Route("/tournament/{tournamentId}/start", name="start-tournament", requirements={"\s"})
     */
    public function startTournament (Request $request, string $tournamentId)
    {
        $tournament = $this->tournamentService->checkTournamentExist($tournamentId);

        if(!$this->ruleServices->canBeStarted($tournament,$this->getUser()->getTFUser())){
            if($request->headers->get(self::KEY_REFERER)) {
                return $this->redirect($request->headers->get(self::KEY_REFERER));
            }
            return $this->redirectToRoute('my_tournament');
        }

        $this->matchService->generateMatches($tournament, true);

        $tournament->setStatus(TournamentStatusEnum::STATUS_STARTED);
        $this->entityManager->persist($tournament);

        $this->entityManager->flush();

        $this->addFlash('success', 'tournament.started');
        return $this->redirectToRoute('my_tournament');
    }

    /**
     * @param Request $request
     * @param string $tournamentId
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     *
     * @Route("/tournament/{tournamentId}/cancel", name="cancel-tournament", requirements={"\s"})
     */
    public function cancelTournament (Request $request, string $tournamentId)
    {
        $tournament = $this->tournamentService->checkTournamentExist($tournamentId);

        if(!$this->ruleServices->canBeCancelled($tournament,$this->getUser()->getTFUser())){
            if($request->headers->get(self::KEY_REFERER)) {
                return $this->redirect($request->headers->get(self::KEY_REFERER));
            }
            return $this->redirectToRoute('my_tournament');
        }

        $tournament->setStatus(TournamentStatusEnum::STATUS_CANCELED);
        $this->entityManager->persist($tournament);
        $this->entityManager->flush();

        $this->addFlash('success', 'tournament.canceled');

        return $this->redirectToRoute('my_tournament');
    }

    /**
     * @param string $tournamentId
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @Route("/tournament/{tournamentId}/detail", name="detail-tournament", requirements={"\s"})
     */
    public function detailTournament(string $tournamentId) : Response
    {
        $form = $this->createForm(ScoreType::class);
        $tournament = $this->tournamentService->checkTournamentExist($tournamentId);
        $matchesPerTurn = $this->matchService->getMatchPerRound($tournament);
        return $this->render('tournament/details.html.twig', [
            'tournament' => $tournament,
            'matchesPerTurn' => $matchesPerTurn,
            'scorForm' => $form->createView()
        ]);
    }

    /**
     * @param Request $request
     * @param string $tournamentId
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     *
     * @Route("/tournament/{tournamentId}/edit", name="edit-tournament", requirements={"\s"})
     */
    public function editTournament(Request $request, string $tournamentId)
    {
        $tournament = $this->tournamentService->checkTournamentExist($tournamentId);

        $form = $this->createForm(TFTournamentType::class, $tournament);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {

            $this->entityManager->persist($tournament);
            $this->entityManager->flush();

            $this->addFlash('success', 'tournament.edit.message');

            return $this->redirectToRoute('my_tournament');
        }

        return $this->render('tournament/edit-tournament.html.twig', [
            'form' => $form->createView(),
            'name' => $tournament->getName(),
        ]);
    }

    /**
     * @param Request $request
     * @param string $tournamentId
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     *
     * @Route("/tournament/{tournamentId}/manageParticipant", name="manage-participant", requirements={"\s"})
     */
    public function manageTournament(Request $request, string $tournamentId)
    {
        $tournament = $this->tournamentService->checkTournamentExist($tournamentId);
        $players = $tournament->getPlayers();

        $form = $this->createForm(ManageParticipantType::class);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $values = \explode(',',$request->get('hidden-manage_participant')['tags']);
            if($this->tournamentService->updateTournamentParticipant($values, $tournament)){
                $this->addFlash('success', 'tournament.participant.update');
                return $this->redirectToRoute('my_tournament');
            }

            $this->addFlash('warning', 'tournament.participant.update');
        }
        return $this->render('tournament/manage-participant.html.twig', [
            'form' => $form->createView(),
            'tournament' => $tournament,
            'playerNumber' => \count($players),
        ]);
    }
}
