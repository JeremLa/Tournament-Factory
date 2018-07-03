<?php
namespace App\Tests\Form\Type;


use App\Entity\TFTournament;
use App\Form\Type\TFTournamentType;
use Symfony\Component\Form\Test\TypeTestCase;

class TFTournamentTypeTest extends TypeTestCase
{
    public function testSubmitValidData () {
        $formData = array(
            'name' => 'tournament',
            'maxParticipantNumber' => 16,
        );

        $objectToCompare = new TFTournament('single-elimination');
        $form = $this->factory->create(TFTournamentType::class, $objectToCompare);

        $object = new TFTournament('single-elimination');
        $object->setMaxParticipantNumber(16);
        $object->setName($formData['name']);
        $object->setMaxParticipantNumber($formData['maxParticipantNumber']);


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