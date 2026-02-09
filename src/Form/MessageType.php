<?php

namespace App\Form;

use App\Entity\Channel;
use App\Entity\Message;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MessageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('contenu', TextareaType::class, [
                'label' => 'Message',
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 4,
                    'placeholder' => 'Écrivez votre message... Utilisez #task ou #decision pour taguer'
                ]
            ])
            ->add('attachment', \Symfony\Component\Form\Extension\Core\Type\FileType::class, [
                'label' => 'Pièce jointe (facultatif)',
                'mapped' => false,
                'required' => false,
                'attr' => ['class' => 'form-control']
            ])

            ->add('channel', EntityType::class, [
                'class' => Channel::class,
                'choice_label' => 'nom',
                'label' => 'Channel',
                'attr' => ['class' => 'form-control']
            ])
            ->add('visibility', ChoiceType::class, [
                'label' => 'Visibilité',
                'choices' => [
                    'Tous' => 'All',
                    'Backoffice uniquement' => 'Backoffice',
                ],
                'attr' => ['class' => 'form-control']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Message::class,
        ]);
    }
}
