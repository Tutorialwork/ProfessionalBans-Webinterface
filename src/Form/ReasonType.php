<?php

namespace App\Form;

use App\Entity\Reasons;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReasonType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('Reason', TextType::class, [
                'attr' => [
                    'class' => "form-control"
                ],
                'label' => 'reason'
            ])
            ->add('Time', IntegerType::class, [
                'attr' => [
                    'class' => "form-control",
                    'id' => 'unit',
                    'style' => 'display: block;'
                ],
                'required' => false,
                'label' => 'duration'
            ])
            ->add('UnitType', ChoiceType::class, [
                'choices' => [
                    'minutes' => 0,
                    'hours' => 1,
                    'days' => 2,
                    'Permanent' => 3
                ],
                'attr' => [
                    'class' => "form-control",
                    'onchange' => 'toggleUnitField()'
                ],
                'label' => 'unittype'
            ])
            ->add('Type', ChoiceType::class, [
                'choices' => [
                    'Ban' => 0,
                    'Mute' => 1
                ],
                'attr' => [
                    'class' => "form-control"
                ],
                'label' => 'type'
            ])
            ->add('Perms', TextType::class, [
                'attr' => [
                    'class' => "form-control"
                ],
                'label' => 'Permissions',
                'required' => false
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'save',
                'attr' => [
                    'class' => 'btn btn-primary'
                ]
            ])
        ;
    }
}
