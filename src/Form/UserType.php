<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email')
            ->add('roles')
            ->add('password')
            ->add('phoneNumber')
            ->add('isActive')
            ->add('isVerified')
            ->add('createdAt', null, [
                'widget' => 'single_text'
            ])
            ->add('lastLoginAt', null, [
                'widget' => 'single_text'
            ])
            ->add('nom')
            ->add('prenom')
            ->add('photo')
            ->add('bio')
            ->add('expertise')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
