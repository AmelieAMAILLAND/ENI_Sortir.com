<?php

namespace App\Controller;

use App\Entity\Event;
use App\Form\EventType;
use App\Repository\EventRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/event')]
class EventController extends AbstractController
{
    #[Route('/', name: 'app_event_index', methods: ['GET'])]
    public function index(EventRepository $eventRepository): Response
    {
        return $this->render('event/index.html.twig', [
            'events' => $eventRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_event_new', methods: ['GET', 'POST'])]
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

    #[Route('/{id}', name: 'app_event_show', methods: ['GET'])]
    public function show(Event $event): Response
    {
        return $this->render('event/show.html.twig', [
            'event' => $event,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_event_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Event $event, EntityManagerInterface $entityManager): Response
    {
        $owner = $event->getPlanner();
//        dd($owner);
        if ($owner===$this->getUser()) {
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
        } else{
            $this->addFlash('danger', 'Vous ne pouvez pas modifier cet évènement car vous n\'en êtes pas l\'organisateur' );
            return $this->redirectToRoute('app_event_index');
        }
    }

    #[Route('/{id}', name: 'app_event_delete', methods: ['POST'])]
    public function delete(Request $request, Event $event, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$event->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($event);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_event_index', [], Response::HTTP_SEE_OTHER);
    }

}
