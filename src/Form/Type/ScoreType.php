<?php

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ScoreType extends AbstractType
{
    private const KEY_LABEL = 'label';

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('score1', IntegerType::class, [
                'required' => true,
                self::KEY_LABEL => false,
                'attr' => [
                    'min' => 0
                ]
            ])
            ->add('score2', IntegerType::class, [
                'required' => true,
                self::KEY_LABEL => false,
                'attr' => [
                    'min' => 0
                ]
            ])
            ->add('isOver', CheckboxType::class, [
                self::KEY_LABEL => 'Terminer le match',
                'required' => false
            ])
            ->add('save', SubmitType::class, [
                self::KEY_LABEL => 'form.save'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'score1' => 0,
            'score2' => 0,
            'isOver' => false
        ]);
    }
}
