<?php
namespace App\Tests\Form\Type;


use App\Entity\TFUser;
use App\Entity\User;
use App\Form\Type\SignUpType;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\Validator\Validation;
use Symfony\Component\VarDumper\VarDumper;

class SignUpTypeTest extends TypeTestCase
{
    public function testSubmitValidData () {
        $formData = array(
            'tfuser' => new TFUser(),
            'email' => 'test@test.fr',
        );

        $objectToCompare = new User();
        $form = $this->factory->create(SignUpType::class, $objectToCompare);

        $object = new User();
        $object->setTfUser(new TFUser());
        $object->setEmail($formData['email']);

        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());

        $this->assertEquals($object, $objectToCompare);

        $view = $form->createView();
        $children = $view->children;

        foreach (array_keys($formData) as $key) {
            $this->assertArrayHasKey($key, $children);
        }
    }

    protected function getExtensions()
    {
        return array(new ValidatorExtension(Validation::createValidator()));
    }
}