<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Form type for overtime decrease
 */
class OvertimeDecreaseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('startDate', DateType::class, [
                'attr' => ['id' => 'startDate'],
                'label' => 'form.overtimeDecrease.label.startDate',
                'required' => true,
            ])
            ->add('isMuliDay', CheckboxType::class, [
                'label' => 'form.overtimeDecrease.label.isMultiDay',
                'required' => false,
            ])
            ->add('endDate', DateType::class, [
                'attr' => ['id' => 'endDate'],
                'label_attr' => ['id' => 'endDateLabel'],
                'label' => 'form.overtimeDecrease.label.endDate'
            ])
            ->add('submit', SubmitType::class, ['label' => 'form.overtimeDecrease.label.submit']);
    }
}