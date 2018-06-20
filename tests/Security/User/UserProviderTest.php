<?php
namespace App\Tests\Security\User;

use App\Entity\User;
use App\Security\User\UserProvider;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;

class UserProviderTest extends KernelTestCase
{
    /* @var UserProvider $userProvider*/
    private $userProvider;
    /* @var EntityManagerInterface $entityManager */
    private $entityManager;
    /* @var User $user */
    private $user;

    protected function setUp() {
        parent::setUp();
        $kernel = self::bootKernel();
        $this->entityManager = $kernel->getContainer()->get('doctrine')->getManager();

        $this->userProvider = new UserProvider($this->entityManager);

        $this->user = new User();
        $this->user->setUsername('test@test.fr');
        $this->user->setEmail('test@test.fr');
        $this->user->setPassword('test');

        $this->entityManager->persist($this->user);
        $this->entityManager->flush();
    }

    public function testLoadUserByUsernameNull() {
        $this->expectException(UsernameNotFoundException::class);
        $this->userProvider->loadUserByUsername(null);
    }

    public function testLoadUserByUsernameFakeUsername() {
        $this->expectException(UsernameNotFoundException::class);
        $this->userProvider->loadUserByUsername('ksjgsbkxvnnxvknxkvnjsjkdnkjnskjdnsfkjsdnf');
    }

    public function testLoadUserByUsernameSuccess() {
        $this->assertInstanceOf(
            User::class,
            $this->userProvider->loadUserByUsername('test@test.fr'));
    }

    public function testRefreshUserBadParamType () {
        $object = new UserTest;

        $this->expectException(UnsupportedUserException::class);
        $this->userProvider->refreshUser($object);
    }

    public function testRefreshUserSuccess () {
        $this->assertInstanceOf(
            User::class,
            $this->userProvider->refreshUser($this->user));
    }

    public function testSupportsClassFail () {
        $this->assertFalse($this->userProvider->supportsClass('class'));
    }

    public function testSupportsClassSuccess () {
        $this->assertTrue($this->userProvider->supportsClass(User::class));
    }

    public function tearDown()
    {
        $tfUser = $this->user->getTfUser();
        $this->entityManager->remove($this->user);
        $this->entityManager->remove($tfUser);
        $this->entityManager->flush();
        parent::tearDown();
    }
}

/**
 * Class UserTest
 * @package App\Tests\Security\User
 */
class UserTest implements UserInterface {

    public function getRoles()
    {
        // no need to implemant
    }


    public function getPassword()
    {
        // no need to implemant
    }


    public function getSalt()
    {
        // no need to implemant
    }


    public function getUsername()
    {
        // no need to implemant
    }


    public function eraseCredentials()
    {
        // no need to implemant
    }
}