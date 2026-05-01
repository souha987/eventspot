<?php

namespace App\Form;

use App\Entity\TagEvenement;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TagEvenementType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, ['label' => 'Nom du tag', 'attr' => ['class' => 'form-control']])
            ->add('couleur', TextType::class, [
                'label' => 'Couleur (ex: #FF5733)',
                'attr' => ['class' => 'form-control', 'placeholder' => '#RRGGBB'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => TagEvenement::class]);
    }
}