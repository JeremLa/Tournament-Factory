<?php
namespace App\Tests\Form\Type;


use App\Entity\TFUser;
use App\Form\Type\TFUserType;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\VarDumper\VarDumper;

class TFUserTypeTest extends TypeTestCase
{
    public function testSubmitValidData () {
        $formData = array(
            'nickname' => 'nickname',
            'firstname' => 'firstname',
            'lastname' => 'lastname',
            'country' => 'FR',
        );

        $objectToCompare = new TFUser();
        $form = $this->factory->create(TFUserType::class, $objectToCompare);

        $object = new TFUser();
        $object->setFirstname($formData['firstname']);
        $object->setLastname($formData['lastname']);
        $object->setCountry($formData['country']);

        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());

        $this->assertEquals($object, $objectToCompare);

        $view = $form->createView();
        $children = $view->children;

        foreach (array_keys($formData) as $key) {
            $this->assertArrayHasKey($key, $children);
        }
    }
}