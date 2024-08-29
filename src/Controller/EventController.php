<?php

namespace App\Controller;

use App\DTO\filtersDTO;
use App\Entity\Event;
use App\Form\EventType;
use App\Repository\EventRepository;
use App\Repository\SiteRepository;
use DateInterval;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;

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
        $filtersDTO = new FiltersDTO(null,null,null,null,null,null,null,null,null);

        $filters = $request->query->all();
        if($filters) {
            foreach ($filters as $key => $value) {
                $filtersDTO->$key = $value;
            }
        }

        $events = $eventRepository->findWithMultipleFilters($filtersDTO);

        $statusArray = ['published', 'in_progress'];

        $sites = $siteRepository->findAll();
        
        return $this->render('event/index.html.twig', [
            'events' => $events,
            'statusArray' => $statusArray,
            'sites'=>$sites,
            'filters'=>$filtersDTO
        ]);
    }

    #[Route('/new', name: '_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $event = new Event();
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

    #[Route('/{id}', name: '_show', methods: ['GET'])]
    public function show(EventRepository $eventRepository, int $id): Response
    {
        $event=$eventRepository->findByIdWithRegistered($id);
//        $event = $eventRepository->findOneById($id);

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



    #[Route('/{id}/edit', name: '_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Event $event, EntityManagerInterface $entityManager): Response
    {
        $owner = $event->getPlanner();
        if ($owner===$this->getUser() && ($event->getState()==='created' || $event->getState()==='published')) {
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
        }
        elseif ($owner!==$this->getUser()){
            $this->addFlash('danger', 'Vous ne pouvez pas modifier cet évènement car vous n\'en êtes pas l\'organisateur' );
            return $this->redirectToRoute('app_event_index');
        } else {
            $this->addFlash('danger', 'Vous ne pouvez pas modifier cet évènement car il est déjà passé ou annulé' );
            return $this->redirectToRoute('app_event_index');
        }

    }

    #[Route('/{id}', name: '_delete', methods: ['POST'])]
    public function delete(Request $request, Event $event, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$event->getId(), $request->getPayload()->getString('delete_token'))) {
            $entityManager->remove($event);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_event_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/cancel', name: '_cancel', methods: ['POST'])]
    public function cancel(Request $request, Event $event, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('cancel'.$event->getId(), $request->getPayload()->getString('cancel_token'))) {
            $event->setState('canceled');
            $entityManager->persist($event);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_event_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/register', name: '_register')]
    public function register(Event $event, EntityManagerInterface $entityManager): Response
    {
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

    #[Route('/{id}/unregister', name: '_unregister')]
    public function unregister(Event $event, EntityManagerInterface $entityManager): Response
    {
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
