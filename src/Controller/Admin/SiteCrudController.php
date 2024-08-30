<?php

namespace App\Controller\Admin;

use App\Entity\Site;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class SiteCrudController extends AbstractCrudController
{
    /**
     * @return string
     */
    public static function getEntityFqcn(): string
    {
        return Site::class;
    }

    /**
     * @param string $pageName
     * @return iterable
     * Configure les champs
     */
    public function configureFields(string $pageName): iterable
    {
        // Définition des champs dans la vue de base

        $fields = [
            IdField::new('id')->hideOnForm(),

            TextField::new('name', 'Sites')->setRequired(true),

            AssociationField::new('users', "Utilisateurs")
                ->setCrudController(UserCrudController::class)
                ->hideOnForm(),
        ];

        // Définition des champs visible uniquement dans la vue Détail

        if ($pageName === Crud::PAGE_DETAIL) {
            $fields[] = TextField::new('userNames', "Utilisateurs")
                ->setVirtual(true)
                ->formatValue(function ($value, $entity) {
                    return $entity->getUserNames();
                })
                ->onlyOnDetail();
        }

        return $fields;
    }





    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL); // Ajoute une action de détail sur la page d'index
    }

}
