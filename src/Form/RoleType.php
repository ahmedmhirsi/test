<?php

namespace App\Form;

use App\Entity\Role;
use App\Entity\Permission;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RoleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Role Name',
                'attr' => ['class' => 'form-control', 'placeholder' => 'Enter role name'],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
                'attr' => ['class' => 'form-control', 'rows' => 3, 'placeholder' => 'Enter role description'],
            ])
            ->add('permissions', EntityType::class, [
                'class' => Permission::class,
                'choice_label' => function(Permission $permission) {
                    return sprintf('%s - %s', $permission->getResource(), $permission->getAction());
                },
                'multiple' => true,
                'expanded' => true,
                'label' => 'Permissions',
                'group_by' => function(Permission $permission) {
                    return $permission->getResource();
                },
                'attr' => ['class' => 'permission-checkboxes'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Role::class,
        ]);
    }
}
