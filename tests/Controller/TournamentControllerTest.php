<?php
namespace App\Tests\Controller;


use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Client;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class TournamentControllerTest extends  WebTestCase
{

    /**
     * @var EntityManager $entityManager
     */
    private $entityManager;
    /**
     * @var Client
     */
    private $client = null;

    /**
     * @var User
     */
    private $user;


    public function setUp()
    {
        parent::setUp();
        $this->client = static::createClient();

        $kernel = self::bootKernel();
        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        $this->user = new User();
        $this->user->setEmail('test@test.fr');
        $this->user->setUsername('test');
        $this->user->setPassword('test');
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
        yield ['/tournament/new'];
    }


    /**
     * @dataProvider urlProvider
     */
    public function testSecurePageIsLoaded($url)
    {


        $this->logIn($this->user->getUsername(), $this->user->getPassword(), $this->user->getRoles());

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
