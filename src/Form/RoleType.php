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
                'label' => 'View/Edit bans',
            ])
            ->add('mutes', CheckboxType::class, [
                'required' => false,
                'label' => 'View/Edit mutes',
            ])
            ->add('reports', CheckboxType::class, [
                'required' => false,
                'label' => 'View/Edit reports',
            ])
            ->add('unban_requests', CheckboxType::class, [
                'required' => false,
                'label' => 'View/Edit unban requests',
            ])
            ->add('reasons', CheckboxType::class, [
                'required' => false,
                'label' => 'View/Edit reasons',
            ])
            ->add('admin', CheckboxType::class, [
                'required' => false,
                'label' => 'View/Edit admin section',
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
