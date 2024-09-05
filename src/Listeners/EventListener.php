<?php

namespace App\Listeners;

use App\Entity\Event;
use DateInterval;
use DateTime;

class EventListener
{

    public function __construct()
    {}

    public function postLoad(Event $event): void{

        //Si created, le statut ne dépend change pas
        if($event->getState()==='created'){
            $event->setState('created');
            return;
        }

        //Si annulée, le statut ne dépend plus de la date actuelle.
        if($event->getState()==='canceled'){
            $event->setState('canceled');
            return;
        }

        //On return pas car ça peut être amené à modifier en fonction de la date
        if($event->getState()==='published'){
            $event->setState('published');
        }

        $now = (new \DateTime())->modify('+2 hours');

        $dateEventPlusOneMonth = (clone $event->getDateTimeStart())->add(new DateInterval('P1M'));

        //Convertir duration en DateInterval
        $durationTemp = (clone $event->getDuration());
        $hours = (int) $durationTemp->format('H');
        $minutes = (int) $durationTemp->format('i');

        $duration = new \DateInterval("PT{$hours}H{$minutes}M");
        // Crée un intervalle à partir de ces heures et minutes

        $dateEndEvent = (clone $event->getDateTimeStart())->add($duration);


        //dd($event, $now, $dateEventPlusOneMonth, $dateEndEvent, $duration);


        if($now > $dateEventPlusOneMonth){// Si on est après la date de début + 1 mois (considérer la durée aussi ?)
            $event->setState('archived');
        }
        if($now > $dateEndEvent && $now < $dateEventPlusOneMonth){
            $event->setState('past');
        }
        if($now < $dateEndEvent && $now > $event->getDateTimeStart()){
            $event->setState('in_progress');
        }
        if(count($event->getRegistered()) >= $event->getMaxNbRegistration()){ // GOOD
            $event->setState('full');
        }
        if($now < $event->getDateTimeStart() && $now > $event->getRegistrationDeadline()){ // GOOD
            $event->setState('closed');
        }

    }

    public function preUpdate($event): void{

        switch($event->getState()){
            case 'published':
            case 'full':
            case 'past':
            case 'archived':
            case 'closed':
            case 'in_progress':
                $event->setState('published');
                break;
            case 'canceled':
                $event->setState('canceled');
                break;
            case 'created':
                $event->setState('created');
                break;
        }


        //Dans cette fontion, il faut modifier l'état qu'on va envoyé en BDD en fonction de l'état actuel

        // (published, full, past, archived, closed) => 'published'
        // canceled => canceled
        // created => created

    }

}