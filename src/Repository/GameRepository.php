<?php

namespace App\Repository;

use App\Entity\Game;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Game>
 *
 * @method Game|null find($id, $lockMode = null, $lockVersion = null)
 * @method Game|null findOneBy(array $criteria, array $orderBy = null)
 * @method Game[]    findAll()
 * @method Game[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GameRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Game::class);
    }

    public function save(Game $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Game $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return Game[] Returns an array of Game objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('g')
//            ->andWhere('g.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('g.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Game
//    {
//        return $this->createQueryBuilder('g')
//            ->andWhere('g.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }


    public function findWithPagination($page, $limit)
    {
        $qb = $this->createQueryBuilder('s');
        $qb->setFirstResult(($page - 1) * $limit);
        $qb->setMaxResults($limit);
        return $qb->getQuery()->getResult();
    }
    // get all data of gamme entity as status value


    /**
     * Return all comment has status on if status = 'on'
     *
     * @param int $page [page number of result]
     * @param int $limit [limit of result on response]
     * @param string $status ['on' or 'off']
     * @return array
     */
    public function findAlLGame($page, $limit, $status)
    {
        $qb = $this->createQueryBuilder('s');
        $qb->setFirstResult(($page - 1) * $limit);
        $qb->setMaxResults($limit);
        $qb->where("s.status='$status'");
        return $qb->getQuery()->getResult();
    }


    /**
     * Return random game by genre
     *
     * @param string|null $genre [video game genre]
     * @return Game|null [return entity game object]
     */
    public function randomGame(string|null $genre = 'RPG'): ?Game
    {
        return $this->createQueryBuilder('g')
            ->where('g.genre = :gameGenre')
            ->setParameter('gameGenre', $genre)
            ->orderBy('RAND()')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
