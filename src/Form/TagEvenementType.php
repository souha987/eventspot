<?php
namespace App\Form;

use App\Entity\TagEvenement;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TagEvenementType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom')
            ->add('couleur', null, [
                'label' => 'Couleur (ex: #FF5733)',
                'attr' => ['placeholder' => '#FF5733'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => TagEvenement::class,
        ]);
    }
}