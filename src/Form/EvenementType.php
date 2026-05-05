<?php

namespace App\Form;

use App\Entity\Evenement;
use App\Entity\Lieu;
use App\Entity\TagEvenement;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class EvenementType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre')
            ->add('description', TextareaType::class)
            ->add('dateDebut', DateTimeType::class, ['widget' => 'single_text'])
            ->add('dateFin', DateTimeType::class, ['widget' => 'single_text'])
            ->add('capaciteMax')
            ->add('prix', MoneyType::class, [
                'currency' => 'EUR',
                'required' => false,
            ])
            ->add('categorie', ChoiceType::class, [
                'choices' => [
                    'Conférence' => 'conference',
                    'Atelier'    => 'atelier',
                    'Meetup'     => 'meetup',
                    'Formation'  => 'formation',
                    'Concert'    => 'concert',
                ],
            ])
            ->add('statut', ChoiceType::class, [
                'choices' => [
                    'Brouillon' => 'brouillon',
                    'Publié'    => 'publie',
                    'Complet'   => 'complet',
                    'Annulé'    => 'annule',
                ],
            ])
            ->add('lieu', EntityType::class, [
                'class'        => Lieu::class,
                'choice_label' => 'nom',
            ])
            ->add('tags', EntityType::class, [
                'class'        => TagEvenement::class,
                'choice_label' => 'nom',
                'multiple'     => true,
                'expanded'     => true,
                'by_reference' => false,
                'required'     => false,
            ])
                ->add('imageFile', FileType::class, [
            'label'    => 'Image (JPEG, PNG, WebP)',
            'mapped'   => false,
            'required' => false,
            'constraints' => [
                new File([
                    'maxSize' => '2M',
                ])
            ],
        ])
                ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Evenement::class]);
    }
}