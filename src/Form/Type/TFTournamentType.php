<?php

namespace App\Form\Type;

use App\Entity\TFTournament;
use App\Services\Enum\TournamentTypeEnum;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TFTournamentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'tournament.form.name'
            ])
            ->add('maxParticipantNumber', ChoiceType::class, [
                'label' => 'tournament.form.maxParticipant',
                'choices' => [2=>2,4=>4,8=>8,16=>16,32=>32,64=>64]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => TFTournament::class,
        ]);
    }
}
