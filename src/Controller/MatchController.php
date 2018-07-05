<?php
namespace App\Controller;


use App\Entity\TFMatch;
use App\Repository\TFMatchRepository;
use App\Services\MatchService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\VarDumper\VarDumper;

class MatchController extends AbstractController
{
    /* @var EntityManagerInterface $entityManager */
    private $entityManager;
    /* @var MatchService $matchService */
    private $matchService;

    public function __construct(EntityManagerInterface $entityManager, MatchService $matchService)
    {
        $this->entityManager = $entityManager;
        $this->matchService = $matchService;
    }

    /**
     * @param int $identifier
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @Route(path="/match/{identifier}/match-detailed", name="match-details")
     */
    public function updateScore (int $identifier, Request $request)
    {
        $match = $this->matchService->findOr404($identifier);

        /* @var Form $scoreForm */
        $scoreForm = $this->createForm('App\Form\Type\ScoreType', $this->matchService->getScoreForForm($match));
        $scoreForm->handleRequest($request);

        if($scoreForm->isSubmitted()) {
            $this->matchService->updateScore($match, [$scoreForm->get('score1')->getData(), $scoreForm->get('score2')->getData()], $scoreForm->get('isOver')->getData());
        }

        return $this->render('match/details.html.twig', [
            'scoreForm' => $scoreForm->createView(),
            'match' => $match
        ]);
    }

    /**
     * @Route(path="/match/mine", name="my-matches")
     */
    public function myMatches ()
    {
        /** @var TFMatchRepository $repo */
        $repo = $this->entityManager->getRepository(TFMatch::class);

        $matchesNotOver = $repo->findByUserParticipantNotOver($this->getUser()->getTFUser());
        $matchesOver = $repo->findByUserParticipantOver($this->getUser()->getTFUser());
        return $this->render('match/my-matches-list.html.twig', [
            'tfmatchesNotOver' => $matchesNotOver,
            'tfmatchesOver' => $matchesOver,
        ]);
    }
}