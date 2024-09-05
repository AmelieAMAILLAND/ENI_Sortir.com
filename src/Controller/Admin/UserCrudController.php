<?php

namespace App\Controller\Admin;

use Symfony\Component\Form\Extension\Core\Type\FileType;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use App\Service\EmailService;
use App\Repository\UserRepository;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use App\Entity\User;
use App\Entity\Site;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TelephoneField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserCrudController extends AbstractCrudController
{
    private $entityManager;
    private $adminUrlGenerator;
    private $emailService;
    private $passwordHasher;
    private $validator;
    private $resetPasswordHelper;

     /**
     * @param EntityManagerInterface $entityManager
     * @param AdminUrlGenerator $adminUrlGenerator
     */
    public function __construct(EntityManagerInterface $entityManager,
                                AdminUrlGenerator $adminUrlGenerator,
                                UserRepository $userRepository,
                                EmailService $emailService,
                                UserPasswordHasherInterface $passwordHasher,
                                ValidatorInterface $validator,
                                ResetPasswordHelperInterface $resetPasswordHelper)
    {
        $this->entityManager = $entityManager;
        $this->adminUrlGenerator = $adminUrlGenerator;
        $this->emailService = $emailService;
        $this->passwordHasher = $passwordHasher;
        $this->validator = $validator;
        $this->resetPasswordHelper = $resetPasswordHelper;
        $this->userRepository = $userRepository;
    }

    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            AssociationField::new('site')->setRequired(true),
            EmailField::new('email'),
            TextField::new('pseudo'),
            TextField::new('firstName', 'First Name'),
            TextField::new('lastName', 'Last Name'),
            TelephoneField::new('phone', 'Phone'),
            ChoiceField::new('roles')
                ->setChoices([
                    'Admin' => 'ROLE_ADMIN',
                    'User' => 'ROLE_USER',
                ])
                ->allowMultipleChoices()
                ->renderExpanded(),
            BooleanField::new('isActive', 'Active'),
            IntegerField::new('eventCount', 'Inscriptions')->onlyOnIndex(),
        ];
    }

    /**
     * @param Actions $actions
     * @return Actions
     * Configurer les actions disponibles dans l'interface d'administration&
     */
    public function configureActions(Actions $actions): Actions
    {
        // Action d'importation CSV
        $importCsv = Action::new('importCsv', '+ CSV')
            ->linkToCrudAction('importCsv')
            ->createAsGlobalAction()
            ->setCssClass('btn btn-primary');

        return $actions
            ->add(Crud::PAGE_INDEX, $importCsv)
            ->update(Crud::PAGE_INDEX, Action::NEW, function (Action $action) {
                return $action->setLabel('+ utilisateur');
            });
    }

    /**
     * @param Request $request
     * @return Response
     * Importer des données d'utilisateurs à partir d'un fichier CSV.
     */
    public function importCsv(Request $request): Response
    {
        // Création du formulaire
        $form = $this->createFormBuilder()
            ->add('csvFile', FileType::class, [
                'label' => 'CSV File',
                'required' => true,
                'attr' => ['accept' => '.csv'],
            ])
            ->getForm();

        // Gestion de la Requête
        $form->handleRequest($request);

        // Validation du formulaire
        if ($form->isSubmitted() && $form->isValid()) {

            // Traitement du fichier CSV
            $file = $form->get('csvFile')->getData();

            if ($file instanceof UploadedFile) {
                $csvData = array_map('str_getcsv', file($file->getPathname()));

                // Validation et traitement des données.
                if ($csvData) {
                    foreach ($csvData as $row) {
                        if (count($row) != 7) {
                            $this->addFlash('error', 'Le fichier CSV ne contient pas le bon nombre de colonnes.');
                            continue;
                        }

                        list($email, $pseudo, $firstName, $lastName, $phone, $roles, $siteName) = $row;

                        $site = $this->entityManager->getRepository(Site::class)->findOneBy(['name' => $siteName]);

                        if (!$site) {
                            $this->addFlash('error', "Le site avec le nom '$siteName' n'existe pas.");
                            continue;
                        }

                        $user = new User();
                        $user->setEmail($email);
                        $user->setPseudo($pseudo);
                        $user->setFirstName($firstName);
                        $user->setLastName($lastName);
                        $user->setPhone($phone);

                        $temporaryPassword = $this->generateTemporaryPassword();
                        $hashedPassword = $this->passwordHasher->hashPassword($user, $temporaryPassword);
                        $user->setPassword($hashedPassword);

                        $user->setRoles([$roles]);
                        $user->setSite($site);
                        $user->setIsActive(true);

                        // Validation de l'utilisateur
                        $errors = $this->validator->validate($user, null, ['AdminCreation']);
                        if (count($errors) > 0) {
                            foreach ($errors as $error) {
                                $this->addFlash('error', $error->getMessage());
                            }
                            continue;
                        }

                        // Persistance de l'utilisateur
                        $this->entityManager->persist($user);

                        // Génération du token pour la première connexion
                        $resetToken = $this->resetPasswordHelper->generateResetToken($user);
                        $firstLoginUrl = $this->generateUrl('app_reset_password', ['token' => $resetToken->getToken()], UrlGeneratorInterface::ABSOLUTE_URL);

                        // Envoi de l'e-mail de confirmation
                        $this->emailService->sendFirstLoginEmail($user->getEmail(), $user->getPseudo(), $firstLoginUrl);
                    }

                    try {
                        $this->entityManager->flush();
                        $this->addFlash('success', 'Les utilisateurs ont été importés avec succès.');
                    }
                    catch (\Exception $e) {
                        $this->addFlash('error', 'Une erreur est survenue lors de l\'enregistrement des données : ' . $e->getMessage());
                    }

                    // Gestion des erreurs
                }
                else {
                    $this->addFlash('error', 'Le fichier CSV est vide ou mal formaté.');
                }

            }
            else {
                $this->addFlash('error', 'Aucun fichier valide n\'a été uploadé.');
            }

        }
        // Rendu du formulaire
        return $this->render('bundles/EasyAdminBundle/crud/import.html.twig', [
            'form' => $form->createView(),
        ]);
    }


    private function generateTemporaryPassword(): string
    {
        return bin2hex(random_bytes(10)); // Génère un mot de passe de 20 caractères aléatoires
    }

    private function generateFirstLoginToken(User $user): string
    {
        $firstLoginToken = Uuid::v4()->toRfc4122(); // Génération d'un UUID unique
        $user->setPassword($firstLoginToken); // Correctement utiliser setFirstLoginToken
        return $firstLoginToken;
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if ($entityInstance instanceof User) {
            // Générer un mot de passe temporaire aléatoire
            $temporaryPassword = $this->generateTemporaryPassword();

            // Hacher le mot de passe temporaire avec bcrypt
            $hashedPassword = $this->passwordHasher->hashPassword($entityInstance, $temporaryPassword);
            $entityInstance->setPassword($hashedPassword);

            // Valider l'utilisateur avec le groupe de validation "AdminCreation"
            $errors = $this->validator->validate($entityInstance, null, ['AdminCreation']);

            if (count($errors) > 0) {
                foreach ($errors as $error) {
                    $this->addFlash('error', $error->getMessage());
                }
                return;
            }

            // Persister l'utilisateur dans la base de données
            parent::persistEntity($entityManager, $entityInstance);

            // Générer un token pour la première connexion
            $resetToken = $this->resetPasswordHelper->generateResetToken($entityInstance);

            // Générer l'URL pour la première connexion avec le token
            $firstLoginUrl = $this->generateUrl('app_reset_password', ['token' => $resetToken->getToken()], UrlGeneratorInterface::ABSOLUTE_URL);

            // Envoyer l'e-mail de confirmation avec le lien de première connexion
            $this->emailService->sendFirstLoginEmail($entityInstance->getEmail(), $entityInstance->getPseudo(), $firstLoginUrl);
        }
    }

    /**
     * @param EntityManagerInterface $entityManager
     * @param $entityInstance
     * @return void
     * Mettre à jour l'entité "User" dans la base de données.
     */
    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if (!$entityInstance instanceof User) {
            return;
        }

        if (!$entityInstance->getIsActive()) {
            $entityInstance->deactivate();
        }

        $entityManager->persist($entityInstance);
        $entityManager->flush();
    }

}

