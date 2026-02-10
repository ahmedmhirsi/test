<?php

namespace App\Form;

use App\Entity\MarketingCampaign;
use App\Entity\MarketingChannel;
use App\Entity\MarketingLead;
use App\Entity\MarketingMessage;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MarketingLeadType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('companyName', TextType::class, [
                'label' => 'Company Name',
                'attr' => ['placeholder' => 'e.g., TechStartup Inc.'],
            ])
            ->add('contactName', TextType::class, [
                'label' => 'Contact Name',
                'attr' => ['placeholder' => 'e.g., John Doe'],
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email Address',
                'attr' => ['placeholder' => 'john@company.com'],
            ])
            ->add('phone', TelType::class, [
                'label' => 'Phone Number',
                'required' => false,
                'attr' => ['placeholder' => '+216 XX XXX XXX'],
            ])
            ->add('position', ChoiceType::class, [
                'label' => 'Position',
                'choices' => [
                    'CEO' => MarketingLead::POSITION_CEO,
                    'CTO' => MarketingLead::POSITION_CTO,
                    'HR Director' => MarketingLead::POSITION_HR_DIRECTOR,
                    'IT Director' => MarketingLead::POSITION_IT_DIRECTOR,
                    'Other' => MarketingLead::POSITION_OTHER,
                ],
            ])
            ->add('status', ChoiceType::class, [
                'label' => 'Status',
                'choices' => [
                    'New' => MarketingLead::STATUS_NEW,
                    'Contacted' => MarketingLead::STATUS_CONTACTED,
                    'Qualified' => MarketingLead::STATUS_QUALIFIED,
                    'Converted' => MarketingLead::STATUS_CONVERTED,
                    'Lost' => MarketingLead::STATUS_LOST,
                ],
            ])
            ->add('campaign', EntityType::class, [
                'class' => MarketingCampaign::class,
                'choice_label' => 'name',
                'label' => 'Campaign',
                'placeholder' => 'Select a campaign',
            ])
            ->add('channel', EntityType::class, [
                'class' => MarketingChannel::class,
                'choice_label' => 'name',
                'label' => 'Channel',
                'placeholder' => 'Select a channel',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => MarketingLead::class,
        ]);
    }
}
