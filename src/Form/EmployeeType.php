<?php

namespace App\Form;

use App\Entity\Employee;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Employee form type
 */
class EmployeeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username', TextType::class, [
                'required' => true,
                'label' => 'Benutzername',
            ])
            ->add('firstName', TextType::class, [
                'required' => true,
                'label' => 'Vorname',
            ])
            ->add('lastName', TextType::class, [
                'required' => true,
                'label' => 'Nachname',
            ])
            ->add('targetWorkingPresent', CheckboxType::class, [
                'required' => false,
                'label' => 'Variabel angestellt',
            ]);
        $builder->add('targetWorkingHours', NumberType::class, ['required' => false, 'label' => 'Wochenarbeitszeit']);
        $builder->add('targetWorkingTimeBegin', TimeType::class, ['required' => false, 'label' => 'Arbeitsbeginn']);
        $builder->add('targetWorkingTimeEnd', TimeType::class, ['required' => false, 'label' => 'Arbeitsende']);
        $builder->add('submit', SubmitType::class, ['label' => 'Speichern']);
    }


    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => Employee::class,
        ]);
    }
}