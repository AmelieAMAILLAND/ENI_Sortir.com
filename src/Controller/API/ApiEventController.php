<?php

namespace App\Controller\API;

use App\DTO\filtersDTO;
use App\Repository\EventRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class ApiEventController extends AbstractController
{

    #[Route('/api/events/{idUser}/', requirements: ['idUser' => '\d+'], methods: ['GET'])]
    public function index(EventRepository $eventRepository,
                              UserRepository $userRepository,
                              Request $request){

        $user = $userRepository->find($request->get('idUser'));

        $filtersDTO = new FiltersDTO(null,'all','all',null,null,null,null);

        $filters = $request->query->all();
        if($filters) {
            foreach ($filters as $key => $value) {
                if(!($value == null)){ //Si value pas nulle alors on l'affecte (SI "" ALORS ON AFFECTE AUSSI ATTENTION pour le cas sans filtres")
                    $filtersDTO->$key = $value;
                }
            }
        }

        $events = $eventRepository->findWithMultipleFilters($filtersDTO, $user);

        return $this->json($events, 200, [], ['groups' => ['events.index']]);

    }
}