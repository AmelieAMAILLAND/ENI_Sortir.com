<?php

namespace App\Controller;

use App\DTO\filtersDTO;
use App\Entity\Event;
use App\Form\AnnulationType;
use App\Form\EventType;
use App\Repository\EventRepository;
use App\Repository\PlaceRepository;
use App\Repository\SiteRepository;
use DateInterval;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\User\UserInterface;

#[Route('/event', name: 'app_event')]
class EventController extends AbstractController
{
    #[Route('/', name: '_index', methods: ['GET'])]
    public function index(EventRepository $eventRepository,
                          SiteRepository $siteRepository,
                          Request $request,
                         // #[MapQueryString] filtersDTO $filtersDTO
    ): Response
    {
        $user = $this->getUser();

        $filtersDTO = new FiltersDTO(null,$user->getSite()->getName(),'Ouverte',null,null,null,null,null);

        $filters = $request->query->all();
        if($filters) {
            foreach ($filters as $key => $value) {
                if(!$value == null){ //Si value pas nulle alors on l'affecte (SI "" ALORS ON AFFECTE AUSSI ATTENTION pour le cas sans filtres")
                    $filtersDTO->$key = $value;
                }
            }
        }

        $events = $eventRepository->findWithMultipleFilters($filtersDTO);

        //Parmis tous les évènements récupérés, on les gardes tous sauf ceux pas encore publiés et dont on n'est pas l'organisateur.
        //Donc on garde ceux qui sont en 'createdé et dont on est l'organisateur ET ceux qui sont pas en 'created'.
        $events = array_filter($events, fn(Event $event) => (($event->getPlanner()->getPseudo() == $user->getPseudo()) && ($event->getState() == 'created')) || ($event->getState() != 'created'));

        $statusArray = ['Ouverte', 'Passée', 'Fermée'];

        $sites = $siteRepository->findAll();
        
        return $this->render('event/index.html.twig', [
            'events' => $events,
            'statusArray' => $statusArray,
            'sites'=>$sites,
            'filters'=>$filtersDTO
        ]);
    }

    #[Route('/new', name: '_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, PlaceRepository $placeRepository, SessionInterface $session): Response
    {
        $event = new Event();
        /*$savedData = $session->get('event_form_data');

        if ($savedData) {
            $event = $savedData;
            $session->remove('event_form_data');
        }*/

        $newPlaceId = $request->query->get('newPlaceId');
        if ($newPlaceId) {
            $newPlace = $placeRepository->find($newPlaceId);
            if ($newPlace) {
                $event->setPlace($newPlace);
            }
        }
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if($form->get('publish')->isClicked()){
                $event->setState('published');
            }else{
                $event->setState('created');
            }
            $event->setPlanner($this->getUser());
            $entityManager->persist($event);
            $entityManager->flush();

            return $this->redirectToRoute('app_event_index', [], Response::HTTP_SEE_OTHER);
        }
        return $this->render('event/edit.html.twig', [
            'event' => $event,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: '_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(EventRepository $eventRepository, int $id): Response
    {
        $event=$eventRepository->findByIdWithRegistered($id);

        if (!$event || $event->getState() === 'created') {
            $this->addFlash('danger', 'Cette sortie n\'est pas visible' );
            return $this->redirectToRoute('app_event_index');
        }
        $now = new \DateTime();
        if ($now > $event->getDateTimeStart()->add(new DateInterval('P1M'))){
            $this->addFlash('danger', 'Cette sortie est archivée' );
            return $this->redirectToRoute('app_event_index');
        }

        return $this->render('event/show.html.twig', [
            'event' => $event,
        ]);
    }



    #[Route('/{id}/edit', name: '_edit', methods: ['GET', 'POST'], requirements: ['id' => '\d+'])]
    public function edit(Request $request, ?Event $event, EntityManagerInterface $entityManager, PlaceRepository $placeRepository): Response
    {
        if (!$event){
            $this->addFlash('danger', 'Vous ne pouvez pas modifier cet évènement car il n\'existe pas');
            return $this->redirectToRoute('app_event_index');
        }
        $owner = $event->getPlanner();
        if ($owner === $this->getUser() && ($event->getState() === 'created' || $event->getState() === 'published')) {
            $newPlaceId = $request->query->get('newPlaceId');
            if ($newPlaceId) {
                $newPlace = $placeRepository->find($newPlaceId);
                if ($newPlace) {
                    $event->setPlace($newPlace);
                }
            }
            $form = $this->createForm(EventType::class, $event);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                if ($form->get('publish')->isClicked()) {
                    $event->setState('published');
                } else {
                    $event->setState('created');
                }
                $entityManager->flush();

                return $this->redirectToRoute('app_event_index', [], Response::HTTP_SEE_OTHER);
            }


            return $this->render('event/edit.html.twig', [
                'event' => $event,
                'form' => $form,
            ]);
        } elseif ($owner !== $this->getUser()) {
            $this->addFlash('danger', 'Vous ne pouvez pas modifier cet évènement car vous n\'en êtes pas l\'organisateur');
            return $this->redirectToRoute('app_event_index');
        } else {
            $this->addFlash('danger', 'Vous ne pouvez pas modifier cet évènement car il est déjà passé ou annulé');
            return $this->redirectToRoute('app_event_index');
        }
    }

    #[Route('/{id}', name: '_delete', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function delete(Request $request, ?Event $event, EntityManagerInterface $entityManager): Response
    {
        if (!$event){
            $this->addFlash('danger', 'Vous ne pouvez pas supprimer cet évènement car il n\'existe pas. Supprimer le néant est compliqué !');
            return $this->redirectToRoute('app_event_index');
        }
        if ($this->isCsrfTokenValid('delete'.$event->getId(), $request->getPayload()->getString('delete_token'))) {
            $entityManager->remove($event);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_event_index', [], Response::HTTP_SEE_OTHER);
    }


    // src/Controller/EventController.php

    #[Route('/{id}/cancel', name: '_cancel', methods: ['GET', 'POST'])]
    public function cancel(Request $request, Event $event, EntityManagerInterface $entityManager): Response
    {
       

    #[Route('/{id}/cancel', name: '_cancel', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function cancel(Request $request, ?Event $event, EntityManagerInterface $entityManager): Response
    {
         // Vérifier si l'utilisateur a les droits nécessaires
        if ($event->getPlanner() !== $this->getUser()) {
            $this->addFlash('danger', 'Vous ne pouvez pas annuler cet évènement car vous n\'en êtes pas l\'organisateur.');
            return $this->redirectToRoute('app_event_index');
        }

        $form = $this->createForm(AnnulationType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $event->setState('canceled');
            $entityManager->flush();

            return $this->redirectToRoute('app_event_index');
        }

        return $this->render('event/cancel.html.twig', [
            'event' => $event,
            'form' => $form->createView(),
        ]);
    }



    #[Route('/{id}/register', name: '_register', requirements: ['id' => '\d+'])]
    public function register(Event $event, EntityManagerInterface $entityManager): Response    
    {
        if (!$event){
            $this->addFlash('danger', 'Vous ne pouvez pas vous inscrire à un évènement inexistant');
            return $this->redirectToRoute('app_event_index');
        }
        $now = new \DateTime();
        if ($event->getRegistrationDeadline() < $now){
            $this->addFlash('danger', 'INSCRIPTION IMPOSSIBLE : date limite d\'inscription dépassée' );
            return $this->redirectToRoute('app_event_index');
        }
        if (count($event->getRegistered())>= $event->getMaxNbRegistration()){
            $this->addFlash('danger', 'INSCRIPTION IMPOSSIBLE : nombre maximum de participant atteint' );
            return $this->redirectToRoute('app_event_index');
        }
        $event->addRegistered($this->getUser());
        $entityManager->flush();
        return $this->redirectToRoute('app_event_show', ['id'=>$event->getId()]);
    }

    #[Route('/{id}/unregister', name: '_unregister', requirements: ['id' => '\d+'])]
    public function unregister(?Event $event, EntityManagerInterface $entityManager): Response
    {
        if (!$event){
            $this->addFlash('danger', 'Vous ne pouvez pas vous désister d\'un évènement inexistant');
            return $this->redirectToRoute('app_event_index');
        }
        $now = new \DateTime();
        if ($event->getRegistrationDeadline() < $now){
            $this->addFlash('danger', 'DÉSISTEMENT IMPOSSIBLE : date limite d\'inscription dépassée' );
            return $this->redirectToRoute('app_event_index');
        }

        $event->removeRegistered($this->getUser());
        $entityManager->flush();
        return $this->redirectToRoute('app_event_show', ['id'=>$event->getId()]);
    }
}
