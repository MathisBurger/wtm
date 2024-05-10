<?php

namespace App\Form;

use App\Entity\ConfiguredWorktime;
use App\Entity\Employee;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
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
                'attr' => [
                    'id' => 'username',
                    'disabled' => $options['data']->getUsername() !== null
                ],
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
            ->add('holidays', NumberType::class, [
                'required' => true,
                'label' => 'Urlaubstage (Jahr)',
            ])
            ->add('isTimeEmployed', CheckboxType::class, [
                'required' => false,
                'label' => 'ist Zeitangestellter',
            ])
            ->add('configuredWorktimes', CollectionType::class, [
                'label' => false,
                'entry_type' => ConfiguredWorktimeType::class,
                'allow_add' => true,
                'prototype' => true,
                'entry_options' => ['label' => false],
                'prototype_data' => new ConfiguredWorktime(),
            ]);
        $builder->add('submit', SubmitType::class, ['label' => 'Speichern']);
    }


    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => Employee::class
        ]);
    }
}