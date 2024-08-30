<?php

namespace App\Controller;

use App\Entity\Place;
use App\Form\PlaceType;
use App\Repository\PlaceRepository;
use App\Service\CallApiService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/place')]
class PlaceController extends AbstractController
{
    #[Route('/', name: 'app_place_index', methods: ['GET'])]
    public function index(PlaceRepository $placeRepository): Response
    {
        return $this->render('place/index.html.twig', [
            'places' => $placeRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_place_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, SessionInterface $session, CallApiService $apiService): Response
    {
        $currentUrl = $request->getUri();
        $referer = $request->headers->get('referer');
        if ($referer && $referer !== $currentUrl) {
            $session->set('previous_url', $referer);
        }

        $place = new Place();
        $form = $this->createForm(PlaceType::class, $place);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $adress=str_replace(' ','+',trim($place->getStreet())).'+'.str_replace(' ','+',trim($place->getCity()));
            $coordinates = $apiService->getCoordinates($adress);
            $place->setLatitude($coordinates['lat']);
            $place->setLongitude($coordinates['lon']);
            $entityManager->persist($place);
            $entityManager->flush();

            $newPlaceId = $place->getId();

            $previousUrl = $session->get('previous_url', $this->generateUrl('app_event_index'));
            $redirectUrl = $previousUrl . (parse_url($previousUrl, PHP_URL_QUERY) ? '&' : '?') . 'newPlaceId=' . $newPlaceId;
            return $this->redirect($redirectUrl);
        }

        return $this->render('place/edit.html.twig', [
            'place' => $place,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_place_show', methods: ['GET'])]
    public function show(Place $place): Response
    {
        return $this->render('place/show.html.twig', [
            'place' => $place,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_place_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Place $place, EntityManagerInterface $entityManager, SessionInterface $session): Response
    {
        $currentUrl = $request->getUri();
        $referer = $request->headers->get('referer');
        if ($referer && $referer !== $currentUrl) {
            $session->set('previous_url', $referer);
        }

        $form = $this->createForm(PlaceType::class, $place);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $newPlaceId = $place->getId();

            $previousUrl = $session->get('previous_url', $this->generateUrl('app_event_index'));
            $redirectUrl = $previousUrl . (parse_url($previousUrl, PHP_URL_QUERY) ? '&' : '?') . 'newPlaceId=' . $newPlaceId;
            return $this->redirect($redirectUrl);
        }

        return $this->render('place/edit.html.twig', [
            'place' => $place,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_place_delete', methods: ['POST'])]
    public function delete(Request $request, Place $place, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$place->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($place);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_place_index', [], Response::HTTP_SEE_OTHER);
    }
}
