<?php

namespace App\Form;

use App\Entity\Jalon;
use App\Entity\Sprint;
use App\Entity\Tache;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use App\Service\UserProviderInterface;

class TacheType extends AbstractType
{
    private UserProviderInterface $userProvider;

    public function __construct(UserProviderInterface $userProvider)
    {
        $this->userProvider = $userProvider;
    }
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre', TextType::class, [
                'label' => 'Titre',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
                'attr' => ['class' => 'form-control', 'rows' => 3],
            ])
            ->add('statut', ChoiceType::class, [
                'label' => 'Statut',
                'choices' => [
                    'À faire' => 'todo',
                    'En cours' => 'in_progress',
                    'En révision' => 'review',
                    'Terminé' => 'done',
                ],
                'attr' => ['class' => 'form-control'],
            ])
            ->add('priorite', ChoiceType::class, [
                'label' => 'Priorité',
                'choices' => [
                    'Basse' => 'basse',
                    'Moyenne' => 'moyenne',
                    'Haute' => 'haute',
                    'Critique' => 'critique',
                ],
                'attr' => ['class' => 'form-control'],
            ])
            ->add('tempsEstime', IntegerType::class, [
                'label' => 'Temps estimé (heures)',
                'attr' => ['class' => 'form-control', 'min' => 1],
            ])
            ->add('tempsReel', IntegerType::class, [
                'label' => 'Temps réel (heures)',
                'required' => false,
                'attr' => ['class' => 'form-control', 'min' => 0],
            ])
            ->add('dateEcheance', DateType::class, [
                'label' => 'Date d\'échéance',
                'widget' => 'single_text',
                'required' => false,
                'attr' => ['class' => 'form-control'],
            ])
            ->add('assigneeId', ChoiceType::class, [
                'label' => 'Assigné à',
                'required' => false,
                'placeholder' => 'Non assigné',
                'choices' => array_flip(array_map(fn($u) => $u['fullName'], array_column($this->userProvider->getAssignableUsers(), null, 'id'))),
                'attr' => ['class' => 'form-control'],
                'help' => 'Sélectionnez le membre de l\'équipe responsable de cette tâche',
            ])
            ->add('ordre', IntegerType::class, [
                'label' => 'Ordre (Kanban)',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'min' => 0,
                ],
            ])
            ->add('sprint', EntityType::class, [
                'class' => Sprint::class,
                'choice_label' => function (Sprint $sprint) {
                    return $sprint->getNom() . ' (' . $sprint->getProjet()->getTitre() . ')';
                },
                'label' => 'Sprint',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('jalon', EntityType::class, [
                'class' => Jalon::class,
                'choice_label' => 'titre',
                'label' => 'Jalon associé',
                'required' => false,
                'placeholder' => 'Aucun jalon',
                'attr' => ['class' => 'form-control'],
                'help' => 'Lier cette tâche à un jalon (3 tâches terminées = jalon validé)',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Tache::class,
        ]);
    }
}
