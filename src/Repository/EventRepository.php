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

    public function findWithMultipleFilters(filtersDTO $filtersDTO): array{

        $query = $this->createQueryBuilder('e')
                ->select('e') // 'COUNT(e.id) as nbParticipant'
                ->addSelect('user')
                ->addSelect('reg_user')
                ->innerJoin('e.planner', 'user')
                ->innerJoin('user.site', 'site')
                ->leftJoin('e.registered', 'reg_user')

                ->addOrderBy('e.dateTimeStart', 'ASC')
                ->addOrderBy('e.registrationDeadline', 'ASC')

                ->andWhere('(e.state = (:published)) OR (e.state IN (:otherState) AND user.pseudo = :requesterPseudo)')
                ->setParameter('published', 'published')
                ->setParameter('otherState', ['canceled','created'])
                ->setParameter('requesterPseudo', $filtersDTO->userPseudo)
//                ->setParameter('states', ['published','created']) // Premier filtre afficher que les états 'published' (en BDD que 'created', 'published' et 'canceled' donc OK)
                ->groupBy('e.id, user.id, site.id, reg_user.id');


            if($filtersDTO->status != 'all'){
                if($filtersDTO->status == 'Ouverte'){
                    $query->having('COUNT(reg_user.id) < e.maxNbRegistration')
                        ->andWhere('e.registrationDeadline > :currentDate')
                        ->setParameter('currentDate', (new \DateTime())->modify('+2 hours'));
                }
                if($filtersDTO->status == 'Passée'){
                    $now = (new \DateTime())->modify('+2 hours'); //Fuseau horaire relou ...
                    $nowMinusOneMonth = (clone $now)->modify('-1 month');

                    $query->andWhere('e.dateTimeStart < :currentDate')
                        ->andWhere('e.dateTimeStart > :oneMonthAgo')
                        ->setParameter('currentDate',$now)
                        ->setParameter('oneMonthAgo',$nowMinusOneMonth);
                }
                if($filtersDTO->status == 'Fermée'){

                    $now = (new \DateTime())->modify('+2 hours');

                    $query->andWhere('e.dateTimeStart > :currentDate')
                        ->andWhere('e.registrationDeadline < :currentDate')
                        ->setParameter('currentDate',$now);
                }
                if($filtersDTO->status == 'Annulée'){
                    $query->andWhere('e.state = :wantedStatus')
                        ->setParameter('wantedStatus', 'canceled');
                }
                if($filtersDTO->status == 'Créée'){
                    $query->andWhere('e.state = :wantedStatus')
                        ->setParameter('wantedStatus', 'created');
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
                ->setParameter('plannerPseudo', $filtersDTO->userPseudo);
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
                    )->setParameter('regUserPseudo', $filtersDTO->userPseudo);

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
                        ->setParameter('regUserPseudo', $filtersDTO->userPseudo);
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
