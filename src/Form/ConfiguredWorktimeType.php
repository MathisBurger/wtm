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
                    'Montag' => ConfiguredWorktime::MONDAY,
                    'Dienstag' => ConfiguredWorktime::TUESDAY,
                    'Mittwoch' => ConfiguredWorktime::WEDNESDAY,
                    'Donnerstag' => ConfiguredWorktime::THURSDAY,
                    'Freitag' => ConfiguredWorktime::FRIDAY,
                    'Samstag' => ConfiguredWorktime::SATURDAY,
                    'Sonntag' => ConfiguredWorktime::SUNDAY
                ],
                'label' => 'Tag',
                'row_attr' => ['class' => 'col-md-2']
            ])
            ->add('regularStartTime', TimeType::class, [
                'label' => 'Startzeit',
                'row_attr' => ['class' => 'col-md-2']
            ])
            ->add('regularEndTime', TimeType::class, [
                'label' => 'Endzeit',
                'row_attr' => ['class' => 'col-md-2']
            ])
            ->add('restrictedStartTime', TimeType::class, [
                'label' => 'eingeschränkte Startzeit',
                'row_attr' => ['class' => 'col-md-2 restrictedTime'],
                'required' => false
            ])
            ->add('restrictedEndTime', TimeType::class, [
                'label' => 'eingeschränkte Endzeit',
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