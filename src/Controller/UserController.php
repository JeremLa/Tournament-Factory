<?php

namespace App\Controller;

use App\Entity\TFTournament;
use App\Entity\TFUser;
use App\Repository\TFTournamentRepository;
use App\Repository\TFUserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class UserController extends Controller
{

    /* @var EntityManagerInterface $entityManager */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/profile", name="profile")
     */
    public function profile()
    {
        return $this->render('user/profile.html.twig', [
            'controller_name' => 'UserController',
            'user' => $this->getUser()->getTFUser(),
        ]);
    }

    /**
 * @Route("/usersNotInTournament/{tournamentId}", name="searchNotInUsers", requirements={"\s"}))
 */
    public function searchUsersNotInTournament(Request $request, string $tournamentId)
    {
        $search = $request->get('search');
        /** @var TFTournamentRepository $repo */
        $repo = $this->entityManager->getRepository(TFTournament::class);

        $tournament = $repo->find($tournamentId);
        /** @var TFUserRepository $repo */
        $repo = $this->entityManager->getRepository(TFUser::class);
        $users = $repo->getUsersNotInTournament($tournament, $search);
        $return = [];
        foreach ($users as $user){
            $return[] = $user->getEmail();
        }
        return new JsonResponse($return);
    }

    /**
     * @Route("/usersInTournament/{tournamentId}", name="searchInUsers", requirements={"\s"}))
     */
    public function searchUsersInTournament(string $tournamentId)
    {
        /** @var TFTournamentRepository $repo */
        $repo = $this->entityManager->getRepository(TFTournament::class);

        $tournament = $repo->find($tournamentId);
        $users = $tournament->getPlayers();
        $return = [];
        foreach ($users as $user){
            array_push($return, $user->getEmail());
        }
        return new JsonResponse($return);
    }
}
