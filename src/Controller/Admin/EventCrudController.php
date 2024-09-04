<?php

namespace App\Controller\Admin;

use App\Entity\Event;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class EventCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Event::class;
    }


    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(), // Cache l'ID sur le formulaire

            TextField::new('name', 'Event Name')
                ->setRequired(true)
                ->setHelp('Le nom de l\'événement doit être unique et contenir entre 4 et 30 caractères'),

            DateTimeField::new('dateTimeStart', 'Date de début')
                ->setFormat('dd/MM/yyyy HH:mm')
                ->setRequired(true)
                ->setHelp('La date et l\'heure de début de l\'événement'),

            DateTimeField::new('duration', 'Durée')
                ->setFormat('HH:mm')
                ->setRequired(false)
                ->setHelp('Durée de l\'événement (optionnelle)'),

            DateTimeField::new('registrationDeadline', 'Date limite d\'inscription')
                ->setFormat('dd/MM/yyyy HH:mm')
                ->setRequired(true)
                ->setHelp('La date limite pour s\'inscrire à l\'événement'),

            IntegerField::new('maxNbRegistration', 'Nombre max de participants')
                ->setRequired(true)
                ->setHelp('Le nombre maximum de participants autorisés'),

            TextareaField::new('infoEvent', 'Informations sur l\'événement')
                ->hideOnIndex()
                ->setRequired(false)
                ->setHelp('Informations supplémentaires concernant l\'événement'),

            ChoiceField::new('state', 'État')
                ->setChoices([
                    'En préparation' => 'draft',
                    'Ouvert aux inscriptions' => 'open',
                    'En cours' => 'ongoing',
                    'Terminé' => 'closed',
                    'Annulé' => 'cancelled',
                ])
                ->setRequired(true)
                ->setHelp('L\'état actuel de l\'événement'),

            AssociationField::new('planner', 'Organisateur')
                ->setRequired(true)
                ->setHelp('L\'utilisateur qui organise l\'événement'),

            AssociationField::new('place', 'Lieu')
                ->setRequired(true)
                ->setHelp('Le lieu où se déroule l\'événement'),

            AssociationField::new('registered', 'Participants')
                ->setCrudController(UserCrudController::class)
                ->hideOnForm() // Cache sur le formulaire car c'est une relation inversée
                ->setHelp('Liste des utilisateurs inscrits à cet événement'),
            
        ];
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL); // Ajoute une action de détail sur la page d'index
    }

}
