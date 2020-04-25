<?php

namespace App\Form;

use App\Entity\Reasons;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EditReasonType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('Reason', TextType::class, [
                'attr' => [
                    'class' => "form-control",
                    'value' => 'test'
                ],
            ])
            ->add('Time', TextType::class, [
                'attr' => [
                    'class' => "form-control"
                ]
            ])
            ->add('Type', ChoiceType::class, [
                'choices' => [
                    'Ban' => 0,
                    'Mute' => 1
                ],
                'attr' => [
                    'class' => "form-control"
                ]
            ])
            ->add('Perms', TextType::class, [
                'attr' => [
                    'class' => "form-control"
                ]
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'add',
                'attr' => [
                    'class' => 'btn btn-primary'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Reasons::class,
        ]);
    }
}
