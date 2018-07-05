<?php
namespace App\Tests\Controller;


use App\Controller\TournamentController;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Services\MatchService;
use App\Services\TournamentRulesServices;
use App\Services\TournamentService;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\ControllerResolver;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Client;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\VarDumper\VarDumper;

class TournamentControllerTest extends  WebTestCase
{

    /* @var EntityManagerInterface $entityManager */
    private $entityManager;
    /* @var Client */
    private $client = null;
    /* @var User */
    private $user;
    /* @var TournamentController $tournamentController */
    private $tournamentController;


    public function setUp()
    {
        parent::setUp();
        $this->client = static::createClient();

        $kernel = self::bootKernel();
        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();
        $session = $kernel->getContainer()
            ->get('session');
        $this->tournamentController = new TournamentController($this->entityManager, new TournamentRulesServices($session),
            new TournamentService($this->entityManager), new MatchService($this->entityManager,$session));

        $this->user = new User();
        $this->user->setEmail('test@test.fr');
        $this->user->setUsername('test');
        $this->user->setPassword('$2y$13$h/C.4YTf9mMgJxEhZ5ccyOaGrJVLEkqVxe0mTb6lwOdj9oYEehQEGA');
        $this->entityManager->persist($this->user);
        $this->entityManager->flush();
    }

    /**
     * @dataProvider urlProvider
     */
    public function testPageIsRedirect($url)
    {
        $this->client->request('GET', $url);
        $this->assertTrue($this->client->getResponse()->isRedirect());
    }

    public function urlProvider()
    {
        yield ['/tournament'];
        yield ['/tournament/create'];
        yield ['/tournament/create/{type}'];
        yield ['/tournament/remove'];
        yield ['/tournament/start'];
        yield ['/tournament/cancel'];
        yield ['/tournament/{tournamentId}/detail'];
        yield ['/tournament/{tournamentId}/edit'];
    }


    /**
     * @dataProvider urlProvider
     */
    public function testSecurePageIsLoaded($url)
    {
        $this->logIn($this->user->getUsername(), 'alex', $this->user->getRoles());

        $this->getUrlAndFollowredirect($url);
        $this->assertTrue($this->client->getResponse()->isSuccessful());
    }

    /**
     * @dataProvider urlProvider
     */
    public function testSecurePageBadUserLogged($url)
    {
        $this->logIn('toto', 'tata',  ['ROLE_USER']);

        $crawler = $this->getUrlAndFollowredirect($url);
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Connexion")')->count()
        );
    }


    public function testChooseTournament ()
    {
        $url = "/tournament/create";
        $this->connect();
        $crawler = $this->client->request('GET', $url);
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Créer un nouveau tournoi")')->count()
        );

    }

    private function connect()
    {
        $crawler = $this->client->request('GET', '/login');

        $form = $crawler->selectButton('Connexion')->form();
        $form['_username'] = 'admin@mail.fr';
        $form['_password'] = 'admin';

        $this->client->submit($form);
        $this->assertTrue($this->client->getResponse()->isRedirect());
        $crawler = $this->client->followRedirect();

        return $crawler;
    }

   private function getUrlAndFollowredirect($url){
       $crawler = $this->client->request('GET', $url);
       while ($crawler->filter('html:contains("Redirecting")')->count() > 0){
           $crawler = $this->client->followRedirect();
       }
       return $crawler;
   }

    private function logIn($username, $password, $roles)
    {
        $token = new UsernamePasswordToken($username, $password, 'main', $roles);
        $session = static::$kernel->getContainer()->get('session');
        $session->set('_security_main', serialize($token));
        $session->save();
    }



    protected function tearDown()
    {
        $userEntity = $this->entityManager->merge($this->user);
        $tfUser = $userEntity->getTfUser();
        $this->entityManager->remove($userEntity);
        $this->entityManager->remove($tfUser);
        $this->entityManager->flush();

        parent::tearDown();
    }
}
