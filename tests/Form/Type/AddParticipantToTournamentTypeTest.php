<?php
namespace App\Tests\Form\Type;


use App\Entity\TFTournament;
use App\Entity\TFUser;
use App\Entity\User;
use App\Form\Type\AddParticipantToTournamentType;
use App\Form\Type\SignUpType;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\Validator\Validation;

class AddParticipantToTournamentTypeTest extends TypeTestCase
{
    public function testSubmitValidData () {
        $formData = array(
        );

        $objectToCompare = new TFTournament('single-elimination');
        $form = $this->factory->create(AddParticipantToTournamentType::class, $objectToCompare);

        $object = new TFTournament('single-elimination');

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
