<?php

namespace App\Controller;

use App\Entity\TFTournament;
use App\Entity\TFUser;
use App\Repository\TFTournamentRepository;
use App\Repository\TFUserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\VarDumper\VarDumper;

class TournamentController extends Controller
{
    /**
     * @var EntityManagerInterface $entityManger
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
         * @var TFTournament[] $tournaments
         */
        $tournaments = $this->entityManger->getRepository('App\Entity\TFTournament')->findAll();

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
        $tournament = new TFTournament($type);
        $form = $this->createForm('App\Form\Type\TFTournamentType', $tournament);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $tournament->setOwner($this->getUser()->getTFUser());

            $this->entityManger->persist($tournament);
            $this->entityManger->flush();

            $this->addFlash('success', 'tournament.new.message');

            return $this->redirectToRoute('chosen-type');
        }

        return $this->render('tournament/new-tournament.html.twig', [
            'form' => $form->createView(),
            'type' => $type
        ]);
    }

    /**
     * @Route("/tournament/{tournamentId}/addParticipant", name="add-participant", requirements={"\s"})
     */
    public function addParticipant (Request $request, string $tournamentId)
    {
        /**
         * @var TFTournamentRepository $repo
         */
        $repo = $this->entityManger->getRepository(TFTournament::class);
        $tournament = $repo->find($tournamentId);
        /**
         * @var TFUser[] $players_in_tournament
         */
        $players_in_tournament = $tournament->getPlayers()->toArray();


        if($tournament == null){
            throw new NotFoundHttpException("Ce tournoi n'existe pas");
        }

        /**
         * @var  TFUserRepository $repo
         */
        $repo = $this->entityManger->getRepository(TFUser::class);
        /**
         * @var Collection $users
         */
        $users = $repo->findAll();

        $form = $this->createForm('App\Form\Type\AddParticipantToTournamentType', $tournament, ['users' => $users, 'players' => $players_in_tournament]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()){
            /**
             * @var ArrayCollection $submited
             */
            $submited = $request->get('add_participant_to_tournament');
            if(array_key_exists('players',$submited)){
                $players_id = $submited['players'];
            }else{
                $players_id = [];
            }
            $players = new ArrayCollection();
            foreach($players_id as $id){
                $players->add($repo->find($id));
            }
            $players_to_delete = array_diff($players_in_tournament, $players->toArray());
            /**
             * @var TFUser $player
             */
            foreach ($players_to_delete as $player){
                $player->removeTournaments($tournament);
                $this->entityManger->persist($player);
                $this->entityManger->flush();
            }
            foreach ($players_id as $id){
                /**
                 * @var TFUser $user
                 */
                $user = $repo->find($id);
                /**
                 * @var TFTournament[] $tournaments
                 */
                $tournaments = $user->getTournaments();

                if(! $tournaments->contains($tournament)){
                    $user->addTournaments($tournament);
                    $this->entityManger->persist($user);
                    $this->entityManger->flush();
                }

            }
            $this->addFlash('success', 'tournament.update.participant');

            return $this->redirectToRoute('my_tournament');
        }

        return $this->render('tournament/add-participant.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
