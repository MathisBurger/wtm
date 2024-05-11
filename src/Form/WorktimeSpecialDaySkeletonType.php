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
                    'form.specialDay.reasonOptions.holiday' => WorktimeSpecialDay::REASON_HOLIDAY,
                    'form.specialDay.reasonOptions.illness' => WorktimeSpecialDay::REASON_ILLNESS
                ],
                'required' => true,
                'label' => 'form.specialDay.label.reason'
            ])
            ->add('startDate', DateType::class, [
                'attr' => ['id' => 'startDate'],
                'label' => 'form.specialDay.label.startDate',
                'required' => true,
            ])
            ->add('isMuliDay', CheckboxType::class, [
                'label' => 'form.specialDay.label.isMultiDay',
                'required' => false,
            ])
            ->add('endDate', DateType::class, [
                'attr' => ['id' => 'endDate'],
                'label_attr' => ['id' => 'endDateLabel'],
                'label' => 'form.specialDay.label.endDate'
            ])
            ->add('notes', TextareaType::class, [
                'label' => 'form.specialDay.label.notes',
                'required' => false,
            ])
            ->add('submit', SubmitType::class, ['label' => 'form.specialDay.label.submit']);
    }

}