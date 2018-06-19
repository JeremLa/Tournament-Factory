<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\VarDumper\VarDumper;

class HomeController extends Controller
{
    /**
     * @Route("/", name="home")
     */
    public function index()
    {
        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }

    /**
     * @Route("/login", name="login")
     */
    public function login(AuthenticationUtils $authenticationUtils)
    {
        $errors = $authenticationUtils->getLastAuthenticationError();

        $lastUsername = $authenticationUtils->getLastUsername();
        return $this->render('home/login.html.twig', [
            'last_username' => $lastUsername,
            'errors' => $errors
        ]);
    }

    /**
     * @Route("/signup", name="signup")
     */
    public function signUp(Request $request, EntityManagerInterface $entityManager, UserPasswordEncoderInterface $encoder)
    {

        $user = new User();

        $form = $this->createForm('App\Form\Type\SignUpType', $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $password = $encoder->encodePassword($user, $user->getPassword());

            $user->setPassword($password);

            $user->getTfUser()->setEmail($user->getEmail());
            $user->setUsername($user->getEmail());
            $user->getTfUser()->addNickname($request->get('sign_up')['tfuser']['nickname']);


            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('login');

        }

        return $this->render('home/signup.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
