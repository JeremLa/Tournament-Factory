<?php
/**
 * Created by PhpStorm.
 * User: AHermes
 * Date: 02/07/2018
 * Time: 18:16
 */

namespace App\Tests\Form\Type;

use App\Form\Type\ManageParticipantType;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\VarDumper\VarDumper;

class ManageParticipantTypeTest extends TypeTestCase
{
    public function testSubmitValidData ()
    {
        $formData = array(
            'tags' => '',
        );

        $form = $this->factory->create(ManageParticipantType::class);

        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());

        $view = $form->createView();
        $children = $view->children;

        foreach (array_keys($formData) as $key) {
            $this->assertArrayHasKey($key, $children);
        }
    }
}
