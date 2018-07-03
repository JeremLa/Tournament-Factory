<?php
namespace App\Controller;


use App\Services\MatchService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
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
     * @Route(path="/match/{id}/match-detailed", name="match-detailed")
     */
    public function updateScore (int $id, Request $request)
    {
        $match = $this->matchService->findOr404($id);

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