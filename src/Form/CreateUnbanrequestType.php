<?php

namespace App\Form;

use App\Entity\Unbans;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CreateUnbanrequestType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('uuid', TextType::class, [
                'label' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'your_name'
                ]
            ])
            ->add('Fair', ChoiceType::class, [
                'label' => false,
                'choices' => [
                    'fair_answer_1' => 1,
                    'fair_answer_0' => 0,
                ],
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'player'
                ]
            ])
            ->add('Message', TextareaType::class, [
                'label' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'message'
                ]
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'save',
                'attr' => [
                    'class' => 'btn btn-primary',
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Unbans::class,
        ]);
    }
}
