<?php

namespace App\Form;

use App\Entity\Reclamation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

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
            ])
            ->add('pieceJointe', FileType::class, [
                'label' => 'Attachment (PDF, Image)',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '5M',
                        'mimeTypes' => [
                            'application/pdf',
                            'image/jpeg',
                            'image/png',
                        ],
                        'mimeTypesMessage' => 'Please upload a valid PDF or Image file',
                    ])
                ],
                'attr' => [
                    'class' => 'file-input'
                ]
            ]);

        // Ajouter statut seulement si demandé (back-office uniquement)
        if ($options['include_status']) {
            $builder->add('statut', ChoiceType::class, [
                'label' => 'Statut',
                'choices' => [
                    'En cours' => 'en_cours',
                    'Répondu' => 'repondu',
                    'Fermée' => 'fermee'
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
            'include_status' => true,    // Par défaut inclure le champ statut
        ]);
    }
}
