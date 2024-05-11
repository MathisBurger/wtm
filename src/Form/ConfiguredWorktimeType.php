<?php

namespace App\Form;

use App\Entity\ConfiguredWorktime;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form type for configured work times
 */
class ConfiguredWorktimeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('dayName', ChoiceType::class, [
                'choices' => [
                    'form.configuredWorktime.dayName.monday' => ConfiguredWorktime::MONDAY,
                    'form.configuredWorktime.dayName.tuesday' => ConfiguredWorktime::TUESDAY,
                    'form.configuredWorktime.dayName.wednesday' => ConfiguredWorktime::WEDNESDAY,
                    'form.configuredWorktime.dayName.thursday' => ConfiguredWorktime::THURSDAY,
                    'form.configuredWorktime.dayName.friday' => ConfiguredWorktime::FRIDAY,
                    'form.configuredWorktime.dayName.saturday' => ConfiguredWorktime::SATURDAY,
                    'form.configuredWorktime.dayName.sunday' => ConfiguredWorktime::SUNDAY
                ],
                'label' => 'form.configuredWorktime.label.day',
                'row_attr' => ['class' => 'col-md-2']
            ])
            ->add('regularStartTime', TimeType::class, [
                'label' => 'form.configuredWorktime.label.regularStartTime',
                'row_attr' => ['class' => 'col-md-2']
            ])
            ->add('regularEndTime', TimeType::class, [
                'label' => 'form.configuredWorktime.label.regularEndTime',
                'row_attr' => ['class' => 'col-md-2']
            ])
            ->add('restrictedStartTime', TimeType::class, [
                'label' => 'form.configuredWorktime.label.restrictedStartTime',
                'row_attr' => ['class' => 'col-md-2 restrictedTime'],
                'required' => false
            ])
            ->add('restrictedEndTime', TimeType::class, [
                'label' => 'form.configuredWorktime.label.restrictedEndTime',
                'row_attr' => ['class' => 'col-md-2 restrictedTime'],
                'required' => false
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ConfiguredWorktime::class,
            'attr' => ['class' => 'row']
        ]);
    }
}