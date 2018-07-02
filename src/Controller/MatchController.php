<?php
namespace App\Controller;


use App\Services\MatchService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
        $match = $this->entityManager->getRepository('App:TFMatch')->find($id);

        if(!$match) {
            throw new NotFoundHttpException('Ce match n\'existe pas.');
        }

        $scoreForm = $this->createForm('App\Form\Type\ScoreType', $this->matchService->getScoreForForm($match));
        $scoreForm->handleRequest($request);

        if($scoreForm->isSubmitted()) {
            $this->matchService->updateScore($match, $scoreForm);
        }

        return $this->render('match/detailed.html.twig', [
            'scoreForm' => $scoreForm->createView(),
            'match' => $match
        ]);
    }
}