<?php

namespace App\Controller\Admin;

use App\Entity\Event;
use App\Entity\Site;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;

class DashboardController extends AbstractDashboardController
{
    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {
        $adminUrlGenerator = $this->container->get(AdminUrlGenerator::class);
        return $this->redirect($adminUrlGenerator->setController(UserCrudController::class)->generateUrl());
    }

    public function configureMenuItems(): iterable
    {
        return [
            MenuItem::linkToCrud('Utilisateurs', 'fas fa-users', User::class),
            MenuItem::linkToCrud('Sites', 'fas fa-building', Site::class),
            MenuItem::linkToCrud('Evènements', 'fas fa-building', Event::class),

        ];
    }
}
