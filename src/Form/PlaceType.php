<?php

namespace App\Form;

use App\Entity\Place;
use Symfony\Component\Form\AbstractType;
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
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Place::class,
        ]);
    }
}
