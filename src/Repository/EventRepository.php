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

    public function findByIdWithRegistered(int $id): Event
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

        //dd($filtersDTO);

        $query = $this->createQueryBuilder('e')
                ->select('e') // 'COUNT(e.id) as nbParticipant'
                ->addSelect('user')
                ->addSelect('reg_user')
                ->innerJoin('e.planner', 'user')
                ->innerJoin('user.site', 'site')
                ->leftJoin('e.registered', 'reg_user')

                ->addOrderBy('e.state', 'DESC')
                ->where('e.state IN (:states)')
                ->setParameter('states', ['published','past','canceled','in_progress']);


            if($filtersDTO->status){
                $query->andWhere('e.state = :status')
                        ->setParameter('status', $filtersDTO->status);
            }
            if($filtersDTO->siteName){
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
                    $query->andWhere('reg_user.pseudo = :regUserPseudo')
                        ->setParameter('regUserPseudo', $filtersDTO->userPseudo);
                }
                if($filtersDTO->registered === 'notRegistered'){
                    
                    //TEST CHATGPT (QUEL BOSS)
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
