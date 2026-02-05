<?php

namespace App\Form;

use App\Entity\OffreEmploi;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use App\Entity\Formation;

class OffreEmploiType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('poste', null, ['attr' => ['class' => 'form-control']])
            ->add('description', TextareaType::class, ['attr' => ['rows' => 5, 'class' => 'form-control']])
            ->add('competencesRequises', TextType::class, [
                'help' => 'Séparez les compétences par des virgules (ex: PHP, Symfony)',
                'attr' => ['class' => 'form-control']
            ])
            ->get('competencesRequises')
            ->addModelTransformer(new CallbackTransformer(
                function ($competencesAsArray): string {
                    // transform the array to a string
                    return implode(', ', $competencesAsArray ?? []);
                },
                function ($competencesAsString): array {
                    // transform the string back to an array
                    return array_filter(array_map('trim', explode(',', $competencesAsString)));
                }
            ));

        $builder
            ->add('salaireMin', MoneyType::class, ['currency' => 'USD', 'required' => false, 'attr' => ['class' => 'form-control']])
            ->add('salaireMax', MoneyType::class, ['currency' => 'USD', 'required' => false, 'attr' => ['class' => 'form-control']])
            ->add('typeContrat', ChoiceType::class, [
                'choices' => [
                    'CDI' => 'CDI',
                    'CDD' => 'CDD',
                    'Freelance' => 'Freelance',
                    'Stage' => 'Stage',
                ],
                'attr' => ['class' => 'form-control']
            ])
            ->add('datePublication', DateType::class, [
                'widget' => 'single_text',
                'attr' => ['class' => 'form-control']
            ])
            ->add('statut', ChoiceType::class, [
                'choices' => [
                    'Active' => 'Active',
                    'Closed' => 'Closed',
                    'Draft' => 'Draft',
                ],
                'attr' => ['class' => 'form-control']
            ])
            ->add('formations', EntityType::class, [
                'class' => Formation::class,
                'choice_label' => 'titre',
                'multiple' => true,
                'expanded' => false,
                'required' => false,
                'attr' => ['class' => 'form-control', 'style' => 'height: 100px']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => OffreEmploi::class,
        ]);
    }
}
