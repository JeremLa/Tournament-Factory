<?php

namespace App\Form\Type;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SignUpType extends AbstractType
{
    private const LABEL = 'label';

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username', TextType::class, [
                self::LABEL => 'form.login'
            ])
            ->add('password', RepeatedType::class, [
                self::LABEL => '',
                'type' => PasswordType::class,
                'invalid_message' => 'form.password.no_match',
                'required' => true,
                'first_options'  => array(self::LABEL => 'form.password.name'),
                'second_options' => array(self::LABEL => 'form.password.repeat'),
            ])
            ->add('email', EmailType::class, [
                self::LABEL => 'form.email'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
