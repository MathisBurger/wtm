<?php

namespace App\Form;

use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\File;

/**
 * Request special day form type
 */
class RequestSpecialDayType extends WorktimeSpecialDaySkeletonType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder->add('documentFile', FileType::class, [
           'label' => 'form.specialDayRequest.document',
           'required' => false,
            'constraints' => [
                new File([
                    'maxSize' => '4096k'
                ])
            ]
        ]);
    }
}