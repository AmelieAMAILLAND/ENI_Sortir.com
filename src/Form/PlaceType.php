<?php

namespace App\Form;

use App\Entity\Place;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PlaceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', null,[
                'label' => 'Lieu',
            ])
            ->add('street', null, [
                'label' => 'Rue',
            ])
            ->add('zipCode', null, [
                'label' => 'Code postal',
            ])
            ->add('city', null, [
                'label' => 'Ville',
            ])
            ->add('latitude', null, [
                'label' => 'Latitude',
            ])
            ->add('longitude', null, [
                'label' => 'Longitude',
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Enregistrer',
                'row_attr'=>[
                    'class'=>'bg-amber-400 rounded-md py-1 px-2 max-w-fit mx-auto hover:opacity-80'
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Place::class,
        ]);
    }
}
