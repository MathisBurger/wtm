<?php

namespace App\Form;

use App\Entity\WorktimeSpecialDay;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;

class WorktimeSpecialDaySkeletonType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('reason', ChoiceType::class, [
                'choices' => [
                    'Urlaub' => WorktimeSpecialDay::REASON_HOLIDAY,
                    'Krankheit' => WorktimeSpecialDay::REASON_ILLNESS
                ],
                'required' => true,
                'label' => 'Grund'
            ])
            ->add('startDate', DateType::class, [
                'attr' => ['id' => 'startDate'],
                'label' => 'Startdatum',
                'required' => true,
            ])
            ->add('isMuliDay', CheckboxType::class, [
                'label' => 'Mehrtägig',
                'required' => false,
            ])
            ->add('endDate', DateType::class, [
                'attr' => ['id' => 'endDate'],
                'label_attr' => ['id' => 'endDateLabel'],
                'label' => 'Enddatum'
            ])
            ->add('notes', TextareaType::class, [
                'label' => 'Notes',
                'required' => false,
            ])
            ->add('submit', SubmitType::class, ['label' => 'Anlegen']);
    }

}