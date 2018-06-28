<?php

namespace App\Form\Type;

use App\Entity\TFTournament;
use App\Entity\TFUser;
use App\Repository\TFUserRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\VarDumper\VarDumper;

class AddParticipantToTournamentType extends AbstractType
{
    const PLAYERS = 'players';

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(self::PLAYERS, ChoiceType::class, array(
                'choices' => $options['users'],
                'choice_label' => function(TFUser $choice) {
                    return $choice->getFirstname() . ' ' . $choice->getLastname();
                },
                'choice_value' => function (TFUser $entity = null) {
                    return $entity ? $entity->getId() : '';
                },
                'multiple' => true,
                'mapped' => false,
                'expanded' => true,
                'data' => $options[self::PLAYERS],
            ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => TFTournament::class,
            'users' => null,
            self::PLAYERS => [],
        ]);
    }
}
