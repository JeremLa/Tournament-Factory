<?php
namespace App\Controller;


use App\Services\MatchService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

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
     * @Route(path="/match/{id}/match-detailed", name="match-details")
     */
    public function updateScore (int $identifier, Request $request)
    {
        $match = $this->matchService->findOr404($identifier);

        /* @var Form $scoreForm */
        $scoreForm = $this->createForm('App\Form\Type\ScoreType', $this->matchService->getScoreForForm($match));
        $scoreForm->handleRequest($request);

        if($scoreForm->isSubmitted()) {
            $this->matchService->updateScore($match, $scoreForm);
        }

        return $this->render('match/details.html.twig', [
            'scoreForm' => $scoreForm->createView(),
            'match' => $match
        ]);
    }
}