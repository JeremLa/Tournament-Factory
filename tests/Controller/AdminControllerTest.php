<?php
namespace App\Tests\Controller;


use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AdminControllerTest extends  WebTestCase
{
    /**
     * @dataProvider urlProvider
     */
    public function testPageIsRedirect($url)
    {
        $client = self::createClient();
        $client->request('GET', $url);

        $this->assertTrue($client->getResponse()->isRedirect());
    }

    public function urlProvider()
    {
        yield ['/admin'];
    }
}
