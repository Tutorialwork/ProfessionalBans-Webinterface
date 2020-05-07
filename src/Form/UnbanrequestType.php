<?php

namespace App\Form;

use App\Entity\Unbans;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UnbanrequestType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('endpicker', DateTimeType::class)
            ->add('status', ChoiceType::class, [
                'label' => 'Choice',
                'choices'  => [
                    'Ban aufgehoben' => 1,
                    'Ban verkürzt' => 2,
                    'Abgelehnt' => 3,
                ],
                'attr' => [
                    'class' => 'form-control',
                    'onchange' => 'toggleDateField()',
                ]
            ])
            ->add('submit', SubmitType::class, [
                'attr' => [
                    'class' => 'btn btn-block btn-primary'
                ],
                'label' => 'save'
            ])
        ;
    }
}
