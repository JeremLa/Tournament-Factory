<?php
/**
 * Created by PhpStorm.
 * User: AHermes
 * Date: 19/06/2018
 * Time: 14:58
 */

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

}