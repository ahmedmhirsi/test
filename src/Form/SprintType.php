<?php

namespace App\Form;

use App\Entity\Projet;
use App\Entity\Sprint;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class SprintType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom du Sprint',
                'attr' => ['class' => 'form-control', 'placeholder' => 'ex: Sprint 1 - Core Feature']
            ])
            ->add('dateDebut', DateType::class, [
                'label' => 'Date de début',
                'widget' => 'single_text',
                'attr' => ['class' => 'form-control']
            ])
            ->add('dateFin', DateType::class, [
                'label' => 'Date de fin',
                'widget' => 'single_text',
                'attr' => ['class' => 'form-control']
            ])
            ->add('objectifVelocite', NumberType::class, [
                'label' => 'Objectif Vélocité',
                'required' => false,
                'attr' => ['class' => 'form-control', 'placeholder' => 'ex: 40']
            ])
            ->add('velociteReelle', NumberType::class, [
                'label' => 'Vélocité Réelle',
                'required' => false,
                'attr' => ['class' => 'form-control']
            ])
            ->add('statut', ChoiceType::class, [
                'label' => 'Statut',
                'choices' => [
                    'Planifié' => 'planifie',
                    'Actif' => 'actif',
                    'Terminé' => 'termine',
                ],
                'attr' => ['class' => 'form-control']
            ])
            ->add('projet', EntityType::class, [
                'class' => Projet::class,
                'choice_label' => 'titre',
                'label' => 'Projet associé',
                'attr' => ['class' => 'form-control']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Sprint::class,
        ]);
    }
}
