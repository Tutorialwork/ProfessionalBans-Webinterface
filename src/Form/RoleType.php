<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RoleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('bans', CheckboxType::class, [
                'required' => false,
                'label' => 'View bans',
                'attr' => [
                    'class' => 'custom-control'
                ],
            ])
            ->add('mutes', CheckboxType::class, [
                'required' => false,
                'label' => 'View mutes',
                'attr' => [
                    'class' => ''
                ]
            ])
            ->add('reasons', CheckboxType::class, [
                'required' => false,
                'label' => 'View reasons',
                'attr' => [
                    'class' => ''
                ]
            ])
            ->add('admin', CheckboxType::class, [
                'required' => false,
                'label' => 'View admin section',
                'attr' => [
                    'class' => ''
                ]
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Speichern',
                'attr' => [
                    'class' => 'btn btn-primary'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
