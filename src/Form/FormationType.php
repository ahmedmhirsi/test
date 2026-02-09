<?php

namespace App\Form;

use App\Entity\Formation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FormationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre', null, ['attr' => ['class' => 'form-control']])
            ->add('description', TextareaType::class, ['attr' => ['rows' => 5, 'class' => 'form-control']])
            ->add('dureeHeures', IntegerType::class, ['attr' => ['class' => 'form-control', 'min' => 1]])
            ->add('typeFormation', ChoiceType::class, [
                'choices' => [
                    'Online' => 'Online',
                    'In-Person' => 'In-Person',
                    'Hybrid' => 'Hybrid',
                ],
                'attr' => ['class' => 'form-control']
            ])
            ->add('niveauDifficulte', ChoiceType::class, [
                'choices' => [
                    'Beginner' => 'Beginner',
                    'Intermediate' => 'Intermediate',
                    'Advanced' => 'Advanced',
                ],
                'attr' => ['class' => 'form-control']
            ])
            ->add('dateDebut', DateType::class, [
                'widget' => 'single_text',
                'attr' => ['class' => 'form-control']
            ])
            ->add('dateFin', DateType::class, [
                'widget' => 'single_text',
                'attr' => ['class' => 'form-control']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Formation::class,
            'attr' => ['novalidate' => 'novalidate'],
        ]);
    }
}
