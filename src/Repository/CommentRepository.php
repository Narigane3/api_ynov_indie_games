<?php

namespace App\Repository;

use App\Entity\Comment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Comment>
 *
 * @method Comment|null find($id, $lockMode = null, $lockVersion = null)
 * @method Comment|null findOneBy(array $criteria, array $orderBy = null)
 * @method Comment[]    findAll()
 * @method Comment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CommentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Comment::class);
    }

    public function save(Comment $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Comment $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return Comment[] Returns an array of Comment objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('c.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Comment
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }

    /**
     * Return all comment has status on if status = 'on' by pagination
     *
     * @param int $page [page number of result]
     * @param int $limit [limit of result on response]
     * @return array
     */
    public function findAllByPagination(int $page, int $limit)
    {
        return $this->createQueryBuilder('s')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->where('s.status = :commentStatus')
            ->setParameter('commentStatus', 'on')
            ->getQuery()
            ->getResult();
    }

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
     * Return all game published ordered of popularity by comment number
     * The popularity is defined by number of comment
     *
     * @param bool $order [ 0 = DESC , 1 = ASC] most popular or least popular
     * @param int $page  page start
     * @param int $limit Limit of game display on page
     * @return array
     */
    public function findGameByComment(bool $order, int $page,int $limit): array
    {
        $qb = $this->createQueryBuilder('s');
        $qb->setFirstResult(($page - 1) * $limit);
        $qb->setMaxResults($limit);
        $qb->orderBy("s.f_commentGameId", $order === true ? 'ASC' : 'DESC');
        return $qb->getQuery()->getResult();
    }

}
