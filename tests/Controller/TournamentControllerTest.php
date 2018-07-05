<?php
namespace App\Tests\Controller;


use App\Controller\TournamentController;
use App\Entity\TFTournament;
use App\Entity\TFUser;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Services\Enum\TournamentStatusEnum;
use App\Services\Enum\TournamentTypeEnum;
use App\Services\MatchService;
use App\Services\TournamentRulesServices;
use App\Services\TournamentService;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\ControllerResolver;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Client;
use Symfony\Component\BrowserKit\Cookie;
use function Symfony\Component\Debug\Tests\testHeader;
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
    /* @var TFTournament $tournament */
    private $tournament;


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

        $this->user = new User();
        $this->user->setEmail('test@test.fr');
        $this->user->setUsername('test');
        $this->user->setPassword('$2y$13$h/C.4YTf9mMgJxEhZ5ccyOaGrJVLEkqVxe0mTb6lwOdj9oYEehQEGA');

        $tfuser = new TFUser();
        $tfuser->addNickname('aaaa');
        $this->user->setTfUser($tfuser);
        $this->entityManager->persist($this->user);

        $repo = $this->entityManager->getRepository(TFUser::class);
        $user = $repo->findOneBy([
            'email' => 'admin@mail.fr',
        ]);

        $this->tournament = new TFTournament(TournamentTypeEnum::TYPE_SINGLE);
        $this->tournament->setName('tournament');
        $this->tournament->setMaxParticipantNumber(2);
        $this->tournament->setOwner($user);
        $this->entityManager->persist($this->tournament);
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

    public function testCreateTournament ()
    {
        $url = "/tournament/create/single-elimination";
        $this->connect();
        $crawler = $this->client->request('GET', $url);
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Nouveau tournois (Elimination directe)")')->count()
        );

        $form = $crawler->selectButton('Enregistrer')->form();
        $tournament = $form['tf_tournament'];
        $tournament['name']->setValue('toto');
        $crawler = $this->client->submit($form);
        $this->assertTrue($this->client->getResponse()->isRedirect());
        $crawler = $this->client->followRedirect();
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Toto")')->count()
        );
    }

    public function testStartTournamentNotExist ()
    {
        $url = "/tournament/start?tournament-id=9999";
        $this->connect();
        $crawler = $this->client->request('GET', $url);

        $this->assertEquals(404,$this->client->getResponse()->getStatusCode(), $this->client->getResponse()->getContent());
        $this->assertEquals(1, $crawler->filter('h1:contains("Ce tournoi n\'existe pas")')->count());

    }

    public function testStartTournament ()
    {
        $this->connect();
        $url = "/tournament/start?tournament-id=".$this->tournament->getId();

        $newUser = new TFUser();
        $newUser->addNickname('bbbbb');
        $this->tournament->addPlayer($this->user->getTfUser());
        $this->tournament->addPlayer($newUser);
        $this->user->getTfUser()->addTournaments($this->tournament);
        $newUser->addTournaments($this->tournament);
        $this->entityManager->persist($this->user->getTfUser());
        $this->entityManager->persist($newUser);
        $this->entityManager->persist($this->tournament);
        $this->entityManager->flush();


        $crawler = $this->client->request('GET', $url);
        $crawler = $this->client->followRedirect();
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Le tournoi est démarré")')->count()
        );
    }

    public function testStartTournamentNotOwner ()
    {
        $this->connect();
        $url = "/tournament/start?tournament-id=".$this->tournament->getId();

        $crawler = $this->client->request('GET', $url);
        $crawler = $this->client->followRedirect();
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Vous n\'avez pas le nombre minimum de participant dans ce tournois.")')->count()
        );

        $newUser = new TFUser();
        $newUser->addNickname('cccc');
        $this->tournament->addPlayer($this->user->getTfUser());
        $this->tournament->addPlayer($newUser);
        $this->user->getTfUser()->addTournaments($this->tournament);
        $newUser->addTournaments($this->tournament);
        $this->tournament->setOwner($this->user->getTfUser());
        $this->entityManager->persist($this->user->getTfUser());
        $this->entityManager->persist($newUser);
        $this->entityManager->persist($this->tournament);
        $this->entityManager->flush();

        $crawler = $this->client->request('GET', $url);

        $crawler = $this->client->followRedirect();
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Vous devez être propriétaire du tournois pour réaliser cette action.")')->count()

        );
    }



    public function testRemoveTournament ()
    {
        $url = "/tournament/remove?tournament-id=9999";
        $this->connect();

        $crawler = $this->client->request('GET', $url);

        $crawler = $this->client->followRedirect();
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Le tournois n\'existe pas")')->count()
        );

        $url = "/tournament/remove?tournament-id=".$this->tournament->getId();

        $this->tournament->setStatus(TournamentStatusEnum::STATUS_STARTED);
    $this->entityManager->persist($this->tournament);
    $this->entityManager->flush();
        $crawler = $this->client->request('GET', $url);

        $crawler = $this->client->followRedirect();
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("L\'état du tournois ne permet pas cette action.")')->count()
        );

        $this->tournament->setStatus(TournamentStatusEnum::STATUS_SETUP);
        $this->entityManager->persist($this->tournament);
        $this->entityManager->flush();

        $crawler = $this->client->request('GET', $url);

        $crawler = $this->client->followRedirect();

        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Le tournois a été supprimé")')->count()
        );
    }



    private function connect($login = 'admin@mail.fr', $pass = 'admin')
    {
        $crawler = $this->client->request('GET', '/login');

        $form = $crawler->selectButton('Connexion')->form();
        $form['_username'] = $login;
        $form['_password'] = $pass;

        $this->client->submit($form);
        $this->assertTrue($this->client->getResponse()->isRedirect());
        $crawler = $this->client->followRedirect();

        return $crawler;
    }

   private function getUrlAndFollowredirect($url){
       $crawler = $this->client->request('GET', $url);
       return $this->followRedirect($crawler);
   }

   private function followRedirect ($crawler)
   {
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
//        $this->tournament = $this->entityManager->getRepository(TFTournament::class)->find($this->tournament->getId());
//        VarDumper::dump($this->tournament->getMatches());
//        foreach ($this->tournament->getMatches() as $match){
//            $this->entityManager->remove($match);
//
//        }
//        $this->entityManager->flush();
//        $this->entityManager->remove($this->tournament);
//        $userEntity = $this->entityManager->merge($this->user);
//        $tfUser = $userEntity->getTfUser();
        $tfuser = new TFUser();
        $this->entityManager->persist($tfuser);
        $this->entityManager->flush();
        $this->tournament->setOwner($tfuser);
        $this->entityManager->persist($this->tournament);
        $this->entityManager->flush();
        $this->entityManager->remove($this->user);
        $this->entityManager->remove($this->user->getTfUser());
        $this->entityManager->flush();

        parent::tearDown();
    }
}
