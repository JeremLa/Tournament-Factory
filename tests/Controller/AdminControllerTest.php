<?php
namespace App\Tests\Controller;


use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMException;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class AdminControllerTest extends  WebTestCase
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
        $this->user->setRoles(['ROLE_ADMIN']);
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

    /**
     * @dataProvider urlProvider
     */
    public function testSecurePageIsLoaded($url)
    {


       $this->logIn();

        $crawler = $this->client->request('GET', $url);

        while ($crawler->filter('html:contains("Redirecting")')->count() > 0){
            $crawler = $this->client->followRedirect();
        }
        $this->assertTrue($this->client->getResponse()->isSuccessful());
    }

    /**
     * @dataProvider urlProvider
     */
    public function testSecurePageBadUserLogged($url)
    {


        $this->badLogIn();

        $crawler = $this->client->request('GET', $url);
        while ($crawler->filter('html:contains("Redirecting")')->count() > 0){
            $crawler = $this->client->followRedirect();
        }
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Connexion")')->count()
        );
    }

    public function urlProvider()
    {
        yield ['/admin'];
    }

    private function logIn()
    {

        $token = new UsernamePasswordToken($this->user->getUsername(), $this->user->getPassword(), 'main', $this->user->getRoles());
        $session = static::$kernel->getContainer()->get('session');
        $session->set('_security_main', serialize($token));
        $session->save();
    }


    private function badLogIn()
    {

        $token = new UsernamePasswordToken('toto', 'tata', 'main', ['ROLE_ADMIN']);
        $session = static::$kernel->getContainer()->get('session');
        $session->set('_security_main', serialize($token));
        $session->save();
    }


    protected function tearDown()
    {
        $tfUser = $this->user->getTfUser();
        $this->entityManager->remove($this->user);
        $this->entityManager->remove($tfUser);
        $this->entityManager->flush();

        parent::tearDown();
    }
}
