<?php

namespace App\Form;

use App\Entity\Event;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EventType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name',null,[
                'label' => 'Nom de la sortie',
            ])
            ->add('dateTimeStart', null, [
                'widget' => 'single_text',
                'label'=> 'Date et heure de la sortie'
            ])
            ->add('duration', null, [
                'widget' => 'single_text',
                'label' => 'Durée',
                'required' => false
            ])
            ->add('registrationDeadline', null, [
                'widget' => 'single_text',
                'label'=> 'Date limite d\'inscription'
            ])
            ->add('maxNbRegistration', null, [
                'label' => 'Nombre de places'
            ])
            ->add('infoEvent', null, [
                'label' => 'Description et infos',
                'required' => false
            ])
            ->add('place', PlaceType::class,[
                'label' => false,
            ])
            ->add('submit', SubmitType::class,[
                'label'=>'Enregistrer',
                'row_attr'=>[
                    'class'=>'bg-amber-400 rounded-md py-1 px-2 max-w-fit mx-auto hover:opacity-80'
                ],
            ])
            ->add('publish', SubmitType::class,[
                'label'=>'Publier la sortie',
                'row_attr'=>[
                    'class'=>'bg-amber-400 rounded-md py-1 px-2 max-w-fit mx-auto hover:opacity-80'
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Event::class,
        ]);
    }
}
