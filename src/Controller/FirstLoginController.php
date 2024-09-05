<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ChangePasswordFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;

class FirstLoginController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/first-login/{token}", name="app_first_login")
     */
    public function firstLogin(Request $request, string $token, UserPasswordHasherInterface $passwordHasher): Response
    {
        // Trouver l'utilisateur associé à ce token
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['firstLoginToken' => $token]);

        if (!$user) {
            throw $this->createNotFoundException('Ce token de première connexion est invalide.');
        }

        // Formulaire de modification de mot de passe
        $form = $this->createForm(ChangePasswordFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Récupérer et hacher le nouveau mot de passe
            $newPassword = $form->get('plainPassword')->getData();
            $hashedPassword = $passwordHasher->hashPassword($user, $newPassword);
            $user->setPassword($hashedPassword);

            // Suppression du token pour éviter une réutilisation
            $user->setFirstLoginToken(null);

            // Sauvegarder les changements
            $this->entityManager->flush();

            // Rediriger après succès
            return $this->redirectToRoute('app_login');
        }

        return $this->render('security/first_login.html.twig', [
            'resetForm' => $form->createView(),
        ]);
    }
}
