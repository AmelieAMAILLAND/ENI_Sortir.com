<?php

namespace App\Controller;

use App\Form\UpdateProfileType;
use App\Repository\NotificationRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\String\Slugger\SluggerInterface;

class ProfileController extends AbstractController
{
    #[Route('/profil/{id?}', name: 'app_profil', requirements: ['id' => '\d+'])]
    public function profile(Request $request,
                            SessionInterface $session,
                            int $id = null,
                            UserRepository $userRepository): Response
    {

        // Si un ID est fourni, on cherche l'utilisateur correspondant
        if ($id !== null) {
            $user = $userRepository->findWithSiteById($id);
        }else{
            $user = $this->getUser();
        }


        $referer = $request->headers->get('referer');

        if($referer !== 'http://localhost:8000/profil/modifier'){
            $session->set('previous_back_url', $referer);
        }

        $previousUrl = $session->get('previous_back_url');


        if (!$user){
            $this->addFlash('danger', 'Cet utilisateur n\'existe pas');
            return $this->redirectToRoute('app_event_index');
        }
        // Si aucun ID n'est fourni, on utilise l'utilisateur actuellement connecté
        if ($id === null && $user === null) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour accéder à votre profil.');
        }

        

        return $this->render('profile/profile.html.twig', [
            'user' => $user,
            'backLink' => $previousUrl,
        ]);
    }



    #[Route('/profil/modifier', name: 'app_modifierProfile')]
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

            $oldPicture = $user->getProfilePicture();

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


            if ($oldPicture && ($oldPicture !== $user->getProfilePicture())) {
                $filesystem = new Filesystem();
                $filesystem->remove($this->getParameter('uploads_directory').'/'.$oldPicture);
            }

            return $this->redirectToRoute('app_profil');
        }

        return $this->render('profile/modifier_profile.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
        ]);
    }

}
