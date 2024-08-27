<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

class ProfileController extends AbstractController
{
    #[Route('/mon_profile', name: 'app_monProfile')]
    public function monProfile(UserInterface $user): Response
    {
        return $this->render('Profile/mon_profile.html.twig', [
            'user' => $user,
        ]);
    }
}
