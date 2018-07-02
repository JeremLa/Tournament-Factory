<?php
/**
 * Created by PhpStorm.
 * User: AHermes
 * Date: 29/06/2018
 * Time: 14:36
 */

namespace App\Form\Type;


use App\Entity\TFTournament;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ManageParticipantType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('tags', TextType::class, [
                'mapped' => false,
                'attr' => ['class' => 'typeahead tm-input form-control tm-input-info'],
                'label' => 'form.players',
                'required' => false
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([

        ]);
    }
}