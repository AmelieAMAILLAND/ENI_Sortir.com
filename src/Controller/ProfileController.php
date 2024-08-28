<?php

namespace App\Controller;

use App\Form\UpdateProfileType;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\String\Slugger\SluggerInterface;

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

    #[Route('/mon_profile/modifier', name: 'app_modifierProfile')]
    public function edit(Request $request,
                         EntityManagerInterface $entityManager,
                         SluggerInterface $slugger,
                         UserPasswordHasherInterface $passwordHasher): Response
    {
        $user = $this->getUser();

        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $form = $this->createForm(UpdateProfileType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('password')->getData();
            $passwordConfirmation = $form->get('password_confirmation')->getData();


            if ($plainPassword) {
                if ($plainPassword === $passwordConfirmation) {
                    $user->setPassword($passwordHasher->hashPassword($user, $plainPassword));
                }
                else {
                    $this->addFlash('error', 'Les mots de passe ne correspondent pas.');
                    return $this->redirectToRoute('app_modifierProfile');
                }
            }

            if ($request->get('remove_picture')) {
                $user->setProfilePicture(null);
            }
            else {
                $photoFile = $form->get('profilePicture')->getData();
                if ($photoFile) {
                    $newFilename = $slugger->slug(pathinfo($photoFile->getClientOriginalName(), PATHINFO_FILENAME)) . '-' . uniqid() . '.' . $photoFile->guessExtension();
                    $photoFile->move($this->getParameter('uploads_directory'), $newFilename);
                    $user->setProfilePicture($newFilename);
                }
            }

            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('app_monProfile');
        }

        return $this->render('profile/modifier_profile.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
        ]);
    }

}