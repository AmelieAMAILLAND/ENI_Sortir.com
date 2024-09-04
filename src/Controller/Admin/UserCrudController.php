<?php

namespace App\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
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
use Psr\Log\LoggerInterface;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;

use Symfony\Component\HttpFoundation\File\UploadedFile;

class UserCrudController extends AbstractCrudController
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var AdminUrlGenerator
     */
    private $adminUrlGenerator;


    /**
     * @param EntityManagerInterface $entityManager
     * @param AdminUrlGenerator $adminUrlGenerator
     */
    public function __construct(EntityManagerInterface $entityManager, AdminUrlGenerator $adminUrlGenerator)
    {
        $this->entityManager = $entityManager;
        $this->adminUrlGenerator = $adminUrlGenerator;

    }

    /**
     * @return string
     */
    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    /**
     * @param string $pageName
     * @return iterable
     * Configure les champs qui apparaîtront dans l'interface d'administration
     */
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

            TextField::new('password', 'Password')
                ->setFormTypeOption('attr.type', 'password')
                ->onlyOnForms()
                ->hideWhenUpdating()
                ->setHelp('Définir un mot de passe temporaire.'),

            ChoiceField::new('roles')
                ->setChoices([
                    'Admin' => 'ROLE_ADMIN',
                    'User' => 'ROLE_USER',
                ])
                ->allowMultipleChoices()
                ->renderExpanded(),

            BooleanField::new('isActive', 'Active'),

            IntegerField::new('eventCount', 'Inscriptions')
                ->onlyOnIndex(),
        ];

    }

    /**
     * @param Actions $actions
     * @return Actions
     * Configurer les actions disponibles dans l'interface d'administration
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
            })
            ->update(Crud::PAGE_INDEX, Action::DELETE, function (Action $action) {
                return $action->setCssClass('btn')->displayIf(function ($entity) {
                    return $entity->getEventCount() === 0;
                });
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
                        if (count($row) < 9) {
                            $this->addFlash('error', 'Le fichier CSV ne contient pas le bon nombre de colonnes.');
                            continue;
                        }

                        list($email, $pseudo, $firstName, $lastName, $phone, $password, $roles, $isActive, $siteName) = $row;

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
                        $user->setPassword(password_hash($password, PASSWORD_BCRYPT)); // Hachage du mot de passe
                        $user->setRoles([$roles]);
                        $user->setIsActive((bool)$isActive);
                        $user->setSite($site); // Associe l'utilisateur au site trouvé

                        $this->entityManager->persist($user);
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
        //Rendu du formulaire
        return $this->render('bundles/EasyAdminBundle/crud/import.html.twig', [
            'form' => $form->createView(),
        ]);
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

