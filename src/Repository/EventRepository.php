<?php

namespace App\Repository;

use App\DTO\filtersDTO;
use App\Entity\Event;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Event>
 */
class EventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Event::class);
    }

    public function findByStatus($status): array
    {
        return $this->createQueryBuilder('e')
            ->where('e.state = :status')
            ->setParameter('status', $status)
            ->getQuery()
            ->getResult();
    }

    public function findByIdWithRegistered(int $id): ?Event
    {
        return $this->createQueryBuilder('e')
            ->addSelect('user')
            ->leftJoin('e.registered', 'user')
            ->where('e.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }


    public function easyAdminFindAllEvents(){
        return $this->createQueryBuilder('e')
            ->select('e')
            ->addselect('user')
            ->addSelect('site')
            ->addSelect('reg_user')
            ->addSelect('place')
            ->innerJoin('e.planner', 'user')
            ->innerJoin('user.site', 'site')
            ->innerJoin('e.place', 'place')
            ->leftJoin('e.registered', 'reg_user');

    }

    public function findWithMultipleFilters(filtersDTO $filtersDTO, $requester): array{

        $query = $this->createQueryBuilder('e')
                ->select('e') // 'COUNT(e.id) as nbParticipant'
                ->addSelect('user')
                ->addSelect('reg_user')
                ->innerJoin('e.planner', 'user')
                ->innerJoin('user.site', 'site')
                ->leftJoin('e.registered', 'reg_user')

                ->addOrderBy('e.dateTimeStart', 'ASC')
                ->addOrderBy('e.registrationDeadline', 'ASC');


                //Close spécifique selon admin ou non

                    if(in_array('ROLE_ADMIN', $requester->getRoles())){
                        //On met pas de préfiltre sur les évènements, on les voit TOUS
                        $query->groupBy('e.id, user.id, site.id, reg_user.id');
                    }else{
                        $query->andWhere('(e.state = (:published)) OR (e.state IN (:otherState) AND user.pseudo = :requesterPseudo)')
                        ->setParameter('published', 'published')
                        ->setParameter('otherState', ['canceled','created'])
                        ->setParameter('requesterPseudo', $requester->getPseudo())
        //                ->setParameter('states', ['published','created']) // Premier filtre afficher que les états 'published' (en BDD que 'created', 'published' et 'canceled' donc OK)
                        ->groupBy('e.id, user.id, site.id, reg_user.id');
                    }



            if($filtersDTO->status != 'all'){

                //Dans ces trois if, on doit rajouter une condition pour que le statut soit 'published'.
                if($filtersDTO->status == 'Ouverte'){
                    $query->having('COUNT(reg_user.id) < e.maxNbRegistration')
                        ->andWhere('e.registrationDeadline > :currentDate')
                        ->andWhere('e.state = :published')
                        ->setParameter('published', 'published')
                        ->setParameter('currentDate', (new \DateTime())->modify('+2 hours'));
                }
                if($filtersDTO->status == 'Complète'){
                    $query->having('COUNT(reg_user.id) = e.maxNbRegistration')
                        ->andWhere('e.registrationDeadline > :currentDate')
                        ->andWhere('e.state = :published')
                        ->setParameter('published', 'published')
                        ->setParameter('currentDate', (new \DateTime())->modify('+2 hours'));
                }
                if($filtersDTO->status == 'Passée'){
                    $now = (new \DateTime())->modify('+2 hours'); //Fuseau horaire relou ...
                    $nowMinusOneMonth = (clone $now)->modify('-1 month');

                    $query->andWhere('e.dateTimeStart < :currentDate')
                        ->andWhere('e.dateTimeStart > :oneMonthAgo')
                        ->andWhere('e.state = :published')
                        ->setParameter('published', 'published')
                        ->setParameter('currentDate',$now)
                        ->setParameter('oneMonthAgo',$nowMinusOneMonth);
                }
                if($filtersDTO->status == 'Fermée'){

                    $now = (new \DateTime())->modify('+2 hours');

                    $query->andWhere('e.dateTimeStart > :currentDate')
                        ->andWhere('e.registrationDeadline < :currentDate')
                        ->andWhere('e.state = :published')
                        ->setParameter('published', 'published')
                        ->setParameter('currentDate',$now);
                }

                //Dans les deux if suivants on ne rajoute pas la condition sur le statut égal à 'published'
                if($filtersDTO->status == 'Annulée'){
                    $query->andWhere('e.state = :wantedStatus')
                        ->setParameter('wantedStatus', 'canceled');
                }
                if($filtersDTO->status == 'Créée'){
                    $query->andWhere('e.state = :wantedStatus')
                        ->setParameter('wantedStatus', 'created');
                }
                if($filtersDTO->status == 'Archivée'){
                    $now = (new \DateTime())->modify('+2 hours');
                    $nowMinusOneMonth = (clone $now)->modify('-1 month');

                    $query->andWhere('e.dateTimeStart < :oneMonthAgo')
                        ->andWhere('e.state = :published')
                        ->setParameter('published', 'published')
                        ->setParameter('oneMonthAgo',$nowMinusOneMonth);
                }
                //Fonctionne pas ... limité pour faires des opérations entre champs de l'entité (dateStart + duration ...)
//                if($filtersDTO->status == 'En_cours'){
//                    $now = (new \DateTime())->modify('+2 hours');
//
//                    // Ajouter une expression native SQL pour calculer la date de fin
//                    $query->andWhere('e.dateTimeStart <= :currentDate')
//                        ->andWhere("(:currentDate BETWEEN e.dateTimeStart AND (e.dateTimeStart + e.duration::INTERVAL))")
//                        ->setParameter('currentDate', $now);
//                }

            }
            if($filtersDTO->siteName != 'all'){
                $query->andWhere('site.name = :siteName')
                    ->setParameter('siteName', $filtersDTO->siteName);
            }
            if($filtersDTO->nameInput){
                $query->andWhere('e.name LIKE :nameInput')
                    ->setParameter('nameInput', "%$filtersDTO->nameInput%");
            }
            if($filtersDTO->beginDate){
                $query->andWhere('e.dateTimeStart >= :startDate')
                    ->setParameter('startDate', $filtersDTO->beginDate);
            }
            if($filtersDTO->endDate){
                $query->andWhere('e.dateTimeStart <= :endDate')
                    ->setParameter('endDate', $filtersDTO->endDate);
            }
            if($filtersDTO->isPlanner){
                $query->andWhere('user.pseudo = :plannerPseudo')
                ->setParameter('plannerPseudo', $requester->getPseudo());
            }
            if($filtersDTO->registered){
                if($filtersDTO->registered === 'registeredOk'){

                    $subQuery = $this->createQueryBuilder('subRegistered')
                        ->select('1')
                        ->from('App\Entity\Event', 'ev')
                        ->leftJoin('ev.registered', 'registered_user')
                        ->where('ev.id = e.id')
                        ->andWhere('registered_user.pseudo = :regUserPseudo')
                        ->getDQL();

                    $query->andWhere(
                        $query->expr()->exists($subQuery)
                    )->setParameter('regUserPseudo', $requester->getPseudo());

//                    $query->andWhere('reg_user.pseudo = :regUserPseudo')
//                        ->setParameter('regUserPseudo', $filtersDTO->userPseudo);
                }
                if($filtersDTO->registered === 'notRegistered'){

                    $subQuery = $this->createQueryBuilder('sub')
                        ->select('1')
                        ->from('App\Entity\Event', 'ev')
                        ->leftJoin('ev.registered', 'registered_user')
                        ->where('ev.id = e.id')
                        ->andWhere('registered_user.pseudo = :regUserPseudo')
                        ->getDQL();

                    $query->andWhere(
                        $query->expr()->not(
                            $query->expr()->exists($subQuery)
                        )
                    )
                        ->setParameter('regUserPseudo', $requester->getPseudo());
                }
            }
            return $query->getQuery()->getResult();
    }

    //    /**
    //     * @return Event[] Returns an array of Event objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('e')
    //            ->andWhere('e.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('e.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Event
    //    {
    //        return $this->createQueryBuilder('e')
    //            ->andWhere('e.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
