<?php

namespace App\Form;

use App\Entity\Candidature;
use App\Entity\OffreEmploi;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;

class CandidatureType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nomCandidat', null, ['attr' => ['class' => 'form-control']])
            ->add('emailCandidat', EmailType::class, ['attr' => ['class' => 'form-control']])
            ->add('dateDepot', null, [
                'widget' => 'single_text',
                'attr' => ['class' => 'form-control']
            ])
            ->add('cvPath', FileType::class, [
                'label' => 'CV (PDF file)',
                'mapped' => false,
                'required' => false,
                'attr' => ['class' => 'form-control'],
            ])
            ->add('lettreMotivation', TextareaType::class, ['required' => false, 'attr' => ['rows' => 4, 'class' => 'form-control']])
            ->add('scoreMatchingIA', null, ['required' => false, 'attr' => ['placeholder' => 'Calculated by AI', 'class' => 'form-control']])
            ->add('statut', ChoiceType::class, [
                'choices' => [
                    'En attente' => 'En attente',
                    'Entretien' => 'Entretien',
                    'Refusé' => 'Refusé',
                    'Accepté' => 'Accepté',
                ],
                'attr' => ['class' => 'form-control']
            ])
            ->add('offreEmploi', EntityType::class, [
                'class' => OffreEmploi::class,
                'choice_label' => 'poste',
                'placeholder' => 'Choisir une offre...',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez sélectionner une offre d\'emploi.',
                    ]),
                ],
                'attr' => ['class' => 'form-control']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Candidature::class,
            'attr' => ['novalidate' => 'novalidate'],
        ]);
    }
}
