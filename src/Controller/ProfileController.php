<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

class ProfileController extends AbstractController
{
    #[Route('/mon_profile', name: 'app_monProfile')]
    public function monProfile(UserInterface $user): Response
    {
        return $this->render('Profile/profile.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/profil/{id}', name: 'app_profil', requirements: ['id'=>'\d+'])]
    public function showProfil(int $id, UserRepository $userRepository): Response
    {
        $user = $userRepository->find($id);

        return $this->render('Profile/profile.html.twig', [
            'user' => $user,
        ]);
    }
}
