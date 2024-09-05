<?php

namespace App\Form;

use App\Entity\Site;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use App\Entity\User;

class UpdateProfileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('pseudo', TextType::class, [
                'label' => 'Pseudo ',
            ])
            ->add('first_name', TextType::class, [
                'label' => 'Prénom ',
            ])
            ->add('last_name', TextType::class, [
                'label' => 'Nom ',
            ])
            ->add('phone', TextType::class, [
                'label' => 'Téléphone ',
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email ',
            ])
            ->add('password', PasswordType::class, [
                'mapped' => false,
                'required' => false,
                'label' => 'Nouveau mot de passe ',
                'empty_data' => '',
            ])
            ->add('password_confirmation', PasswordType::class, [
                'mapped' => false,
                'required' => false,
                'label' => 'Confirmez le mot de passe '
            ])
            ->add('site', EntityType::class, [
                'label' => 'Site de rattachement ',
                'class' => Site::class,
                'placeholder' => 'Choisissez un site',
                'required' => true,
            ])

            ->add('profilePicture', FileType::class, [
                'label' => 'photo de profil ',
                'required' => false,
                'mapped' => false,
            ])

        ;
    }

    /**
     * @param OptionsResolver $resolver
     * @return void
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
