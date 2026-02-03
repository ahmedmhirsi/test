<?php

namespace App\Form;

use App\Entity\Jalon;
use App\Entity\Projet;
use App\Entity\Sprint;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class JalonType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre', TextType::class, [
                'label' => 'Titre du jalon',
                'attr' => [
                    'placeholder' => 'Ex: Livraison v1.0',
                    'class' => 'form-input w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white focus:ring-primary focus:border-primary',
                ],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
                'attr' => [
                    'rows' => 4,
                    'placeholder' => 'Description détaillée du jalon...',
                    'class' => 'form-textarea w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white focus:ring-primary focus:border-primary',
                ],
            ])
            ->add('dateEcheance', DateType::class, [
                'label' => 'Date d\'échéance',
                'widget' => 'single_text',
                'attr' => [
                    'class' => 'form-input w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white focus:ring-primary focus:border-primary',
                ],
            ])
            ->add('statut', ChoiceType::class, [
                'label' => 'Statut',
                'choices' => [
                    'Planifié' => 'planifie',
                    'En cours' => 'en_cours',
                    'Atteint' => 'atteint',
                    'Retardé' => 'retarde',
                ],
                'attr' => [
                    'class' => 'form-select w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white focus:ring-primary focus:border-primary',
                ],
            ])
            ->add('priorite', ChoiceType::class, [
                'label' => 'Priorité',
                'choices' => [
                    'Basse' => 'basse',
                    'Moyenne' => 'moyenne',
                    'Haute' => 'haute',
                    'Critique' => 'critique',
                ],
                'attr' => [
                    'class' => 'form-select w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white focus:ring-primary focus:border-primary',
                ],
            ])
            ->add('projet', EntityType::class, [
                'class' => Projet::class,
                'choice_label' => 'titre',
                'label' => 'Projet',
                'attr' => [
                    'class' => 'form-select w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white focus:ring-primary focus:border-primary',
                ],
            ])
            ->add('sprint', EntityType::class, [
                'class' => Sprint::class,
                'choice_label' => 'nom',
                'label' => 'Sprint associé',
                'required' => false,
                'placeholder' => 'Aucun sprint',
                'attr' => [
                    'class' => 'form-select w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white focus:ring-primary focus:border-primary',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Jalon::class,
        ]);
    }
}
