<?php

namespace App\Form;

use App\Entity\Reponse;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReponseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('message', TextareaType::class, [
                'label' => 'Message',
                'attr' => [
                    'class' => 'w-full bg-slate-100 dark:bg-slate-800 border-none rounded-lg py-2 px-4 text-sm text-navy dark:text-white focus:ring-2 focus:ring-primary',
                    'placeholder' => 'Votre réponse...',
                    'rows' => 4
                ]
            ])
            ->add('auteur', TextType::class, [
                'label' => 'Auteur',
                'attr' => [
                    'class' => 'w-full bg-slate-100 dark:bg-slate-800 border-none rounded-lg py-2 px-4 text-sm text-navy dark:text-white focus:ring-2 focus:ring-primary',
                    'placeholder' => 'Votre nom'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Reponse::class,
        ]);
    }
}
