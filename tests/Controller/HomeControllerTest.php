<?php
namespace App\Tests\Controller;


use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class HomeControllerTest extends  WebTestCase
{
    /**
     * @dataProvider urlProvider
     */
    public function testPageIsSuccessful($url)
    {
        $client = self::createClient();
        $client->request('GET', $url);

        $this->assertTrue($client->getResponse()->isSuccessful());
    }

    public function urlProvider()
    {
        yield ['/'];
        yield ['/login'];
        yield ['/signup'];
    }

    public function testLoginFailure()
    {
        $client = self::createClient();

        $crawler = $client->request('GET', '/login');

        $form = $crawler->selectButton('Connexion')->form();
        $form['_username'] = 'admin';
        $form['_password'] = '';

        $client->submit($form);
        $this->assertTrue($client->getResponse()->isRedirect());
        $crawler = $client->followRedirect();
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Identifiants invalides.")')->count()
        );
    }

    public function testSignUpNoDataFailure()
    {
        $client = self::createClient();

        $crawler = $client->request('GET', '/signup');

        $form = $crawler->selectButton('Enregistrer')->form();

        $crawler = $client->submit($form);
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Erreur Cette valeur ne doit pas être vide.")')->count()
        );
    }

    public function testSignUpWrongMailFailure()
    {
        $client = self::createClient();

        $crawler = $client->request('GET', '/signup');

        $form = $crawler->selectButton('Enregistrer')->form();
        $form->setValues(array(
            'sign_up[tfuser][firstname]' => 'alex',
            'sign_up[tfuser][lastname]' => 'alex',
            'sign_up[tfuser][nickname]' => 'alex',
            'sign_up[email]' => 'alex',
            'sign_up[password][first]' => 'alex',
            'sign_up[password][second]' => 'alex',
            'sign_up[tfuser][country]' => 'FR',
        ));

        $crawler = $client->submit($form);
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Cette valeur n\'est pas une adresse email valide.")')->count()
        );
    }

    public function testSignUpNotSamePasswordFailure()
    {
        $client = self::createClient();

        $crawler = $client->request('GET', '/signup');

        $form = $crawler->selectButton('Enregistrer')->form();
        $form->setValues(array(
            'sign_up[tfuser][firstname]' => 'alex',
            'sign_up[tfuser][lastname]' => 'alex',
            'sign_up[tfuser][nickname]' => 'alex',
            'sign_up[email]' => 'alex@mail.fr',
            'sign_up[password][first]' => 'alex',
            'sign_up[password][second]' => 'alexis',
            'sign_up[tfuser][country]' => 'FR',
        ));

        $crawler = $client->submit($form);
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Les deux mots de passe doivent être identique")')->count()
        );
    }

    public function testSignUpSuccess()
    {
        $client = self::createClient();

        $crawler = $client->request('GET', '/signup');

        $form = $crawler->selectButton('Enregistrer')->form();
        $form->setValues(array(
            'sign_up[tfuser][firstname]' => 'admin',
            'sign_up[tfuser][lastname]' => 'admin',
            'sign_up[tfuser][nickname]' => 'admin',
            'sign_up[email]' => 'admin@mail.fr',
            'sign_up[password][first]' => 'admin',
            'sign_up[password][second]' => 'admin',
            'sign_up[tfuser][country]' => 'FR',
        ));

        $client->submit($form);
        $this->assertTrue($client->getResponse()->isRedirect());
    }

    public function testSignUpAlreadySignUpFailure()
    {
        $client = self::createClient();

        $crawler = $client->request('GET', '/signup');

        $form = $crawler->selectButton('Enregistrer')->form();
        $form->setValues(array(
            'sign_up[tfuser][firstname]' => 'admin',
            'sign_up[tfuser][lastname]' => 'admin',
            'sign_up[tfuser][nickname]' => 'admin',
            'sign_up[email]' => 'admin@mail.fr',
            'sign_up[password][first]' => 'admin',
            'sign_up[password][second]' => 'admin',
            'sign_up[tfuser][country]' => 'FR',
        ));

        $crawler = $client->submit($form);
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Cet email est déjà utilisé.")')->count()
        );
    }

    public function testLoginSuccess()
    {
        $client = self::createClient();

        $crawler = $client->request('GET', '/login');

        $form = $crawler->selectButton('Connexion')->form();
        $form['_username'] = 'admin';
        $form['_password'] = 'admin';

        $client->submit($form);
        $this->assertTrue($client->getResponse()->isRedirect());
        $crawler = $client->followRedirect();
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Tournois")')->count()
        );
    }
}
