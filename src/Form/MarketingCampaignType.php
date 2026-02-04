<?php

namespace App\Form;

use App\Entity\MarketingCampaign;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MarketingCampaignType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Campaign Name',
                'attr' => ['placeholder' => 'e.g., Q1 LinkedIn Outreach'],
            ])
            ->add('objective', TextareaType::class, [
                'label' => 'Campaign Objective',
                'required' => false,
                'attr' => ['placeholder' => 'Describe the goal of this campaign...', 'rows' => 3],
            ])
            ->add('targetLeads', IntegerType::class, [
                'label' => 'Target Leads',
                'attr' => ['placeholder' => 'e.g., 50'],
            ])
            ->add('startDate', DateType::class, [
                'label' => 'Start Date',
                'widget' => 'single_text',
            ])
            ->add('endDate', DateType::class, [
                'label' => 'End Date',
                'widget' => 'single_text',
                'required' => false,
            ])
            ->add('status', ChoiceType::class, [
                'label' => 'Status',
                'choices' => [
                    'Planned' => MarketingCampaign::STATUS_PLANNED,
                    'Active' => MarketingCampaign::STATUS_ACTIVE,
                    'Paused' => MarketingCampaign::STATUS_PAUSED,
                    'Completed' => MarketingCampaign::STATUS_COMPLETED,
                ],
            ])
            ->add('createdBy', TextType::class, [
                'label' => 'Created By',
                'required' => false,
                'attr' => ['placeholder' => 'Your name'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => MarketingCampaign::class,
        ]);
    }
}
