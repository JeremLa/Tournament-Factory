<?php

namespace App\Form;

use App\Entity\TFUser;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TFUserType extends AbstractType
{
    private const LABEL = 'label';
    private const REQUIRED = 'required';

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
//            ->add('email', EmailType::class)
            ->add('nickname', TextType::class, [
                'mapped' => false,
                self::LABEL => 'form.nickname'
            ])
            ->add('firstname', TextType::class, [
                self::LABEL => 'form.firstname',
                self::REQUIRED => false
            ])
            ->add('lastname', TextType::class, [
                self::LABEL => 'form.lastname',
                self::REQUIRED => false
            ])
            ->add('country', CountryType::class, [
                "preferred_choices" => ['FR', 'GB', 'US'],
                self::LABEL => 'form.country',
                self::REQUIRED => false
            ])
        ;

        if($options['with_email']) {
           $builder->add('email', EmailType::class);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => TFUser::class,
            'with_email' => true
        ]);
    }
}
