<?php

namespace App\Form;

use App\Entity\Bans;
use App\Entity\Reasons;
use App\Repository\ReasonRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MuteType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('Name', TextType::class, [
                'attr' => [
                    'class' => "form-control"
                ],
                'label' => 'player'
            ])
            ->add('Reason', EntityType::class, [
                'class' => Reasons::class,
                'choice_label' => 'reason',
                'attr' => [
                    'class' => 'form-control'
                ],
                'query_builder' => function (ReasonRepository $er) {
                    return $er->createQueryBuilder('r')
                        ->where('r.Type = 1');
                },
                'label' => 'reason'
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Mute',
                'attr' => [
                    'class' => 'btn btn-primary'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Bans::class,
        ]);
    }
}
