<?php

namespace App\Form;

use App\Entity\Reclamation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReclamationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre', TextType::class, [
                'label' => 'Titre',
                'attr' => [
                    'class' => 'w-full bg-slate-100 dark:bg-slate-800 border-none rounded-lg py-2 px-4 text-sm text-navy dark:text-white focus:ring-2 focus:ring-primary',
                    'placeholder' => 'Titre de la réclamation'
                ]
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'attr' => [
                    'class' => 'w-full bg-slate-100 dark:bg-slate-800 border-none rounded-lg py-2 px-4 text-sm text-navy dark:text-white focus:ring-2 focus:ring-primary',
                    'placeholder' => 'Décrivez votre réclamation en détail...',
                    'rows' => 6
                ]
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'attr' => [
                    'class' => 'w-full bg-slate-100 dark:bg-slate-800 border-none rounded-lg py-2 px-4 text-sm text-navy dark:text-white focus:ring-2 focus:ring-primary',
                    'placeholder' => 'votre@email.com'
                ]
            ]);

        // Ajouter statut et priorité seulement si demandé (back-office uniquement)
        if ($options['include_status']) {
            $builder->add('statut', ChoiceType::class, [
                'label' => 'Statut',
                'choices' => [
                    'Ouverte' => 'ouverte',
                    'En cours' => 'en_cours',
                    'Fermée' => 'fermee'
                ],
                'attr' => [
                    'class' => 'w-full bg-slate-100 dark:bg-slate-800 border-none rounded-lg py-2 px-4 text-sm text-navy dark:text-white focus:ring-2 focus:ring-primary'
                ]
            ]);
        }

        if ($options['include_priority']) {
            $builder->add('priorite', ChoiceType::class, [
                'label' => 'Priorité',
                'choices' => [
                    'Faible' => 'faible',
                    'Moyenne' => 'moyenne',
                    'Haute' => 'haute'
                ],
                'attr' => [
                    'class' => 'w-full bg-slate-100 dark:bg-slate-800 border-none rounded-lg py-2 px-4 text-sm text-navy dark:text-white focus:ring-2 focus:ring-primary'
                ]
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Reclamation::class,
            'include_status' => true,    // Par défaut inclure tous les champs
            'include_priority' => true,  // Par défaut inclure tous les champs
        ]);
    }
}
