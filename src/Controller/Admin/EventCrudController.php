<?php

namespace App\Controller\Admin;

use App\Entity\Event;
use App\Service\EmailService;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\EventRepository;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class EventCrudController extends AbstractCrudController
{

    private EmailService $emailService;
       
    public function __construct(EventRepository $eventRepository, EmailService $emailService){
        $this->eventRepository = $eventRepository;
        $this->emailService = $emailService;

    }

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

            IntegerField::new('maxNbRegistration', 'max participants')
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

        // Crée une nouvelle action pour annuler un événement
        $cancelEvent = Action::new('cancelEventForm', 'Annuler')
            ->linkToCrudAction('cancelEventForm') // Lien vers l'action CRUD qui affiche le formulaire
            ->setCssClass('btn-secondary');

        return $actions
            ->add(Crud::PAGE_INDEX, $cancelEvent)   // Afficher le bouton "Annuler" sur la page d'index
            ->add(Crud::PAGE_DETAIL, $cancelEvent)
            ->add(Crud::PAGE_INDEX, Action::DETAIL);
    }

    public function cancelEventForm(AdminContext $context): Response
    {
        $event = $context->getEntity()->getInstance();

        // Rendre le formulaire Twig pour saisir le motif d'annulation
        return $this->render('admin/event_cancel_form.html.twig', [
            'event' => $event
        ]);
    }

    public function confirmCancel(AdminContext $context, Request $request, EntityManagerInterface $entityManager): Response
    {
        $event = $context->getEntity()->getInstance();
        $motifAnnulation = $request->request->get('motif_annulation');

        if (!$motifAnnulation) {
            $this->addFlash('danger', 'Le motif d\'annulation est obligatoire.');
            return $this->render('admin/event_cancel_form.html.twig', [
                'event' => $event,
            ]);
        }

        // Enregistrer l'état annulé et le motif d'annulation
        $event->setState('cancelled');
        $event->setAnnulation($motifAnnulation);
        $entityManager->flush();

        // Envoi des e-mails aux participants
        $participants = $event->getRegistered(); // Récupère les utilisateurs inscrits à l'événement

        foreach ($participants as $participant) {
            $this->emailService->sendCancellationEmail(
                $participant->getEmail(),        // Email du participant
                $event->getName(),               // Nom de l'événement
                $motifAnnulation                 // Motif d'annulation
            );
        }

        // Message de succès
        $this->addFlash('success', 'L\'événement a été annulé avec succès et les participants ont été informés.');

        return $this->redirect($this->generateUrl('admin', [
            'crudAction' => 'index',
            'crudControllerFqcn' => EventCrudController::class,
        ]));
    }

    public function createIndexQueryBuilder(SearchDto $searchDto,
                                            EntityDto $entityDto,
                                            FieldCollection $fields,
                                            FilterCollection $filters): QueryBuilder
    {
        $response = $this->eventRepository->easyAdminFindAllEvents(); // TODO: Change the autogenerated stub
        return $response;
    }

}
