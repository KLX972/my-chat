<?php

namespace App\Repository;

use App\Entity\Conversation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Query\Expr\Join;

/**
 * @method Conversation|null find($id, $lockMode = null, $lockVersion = null)
 * @method Conversation|null findOneBy(array $criteria, array $orderBy = null)
 * @method Conversation[]    findAll()
 * @method Conversation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ConversationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Conversation::class);
    }

    // /**
    //  * @return Conversation[] Returns an array of Conversation objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Conversation
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
    public function findConversationByParticipants(int $otherUserId, int $myId)
    {
        $qb =$this->createQueryBuilder('c');
        return $qb->innerJoin('c.participants', 'p')
            ->select('count(p.conversation)')
            ->where($qb->expr()->eq('p.user', ':me'))
            ->orWhere($qb->expr()->eq('p.user', ':otherUser'))
            ->groupBy('p.conversation')
            ->having($qb->expr()->eq('count(p.conversation)', '2'))
            ->setParameter('me', $myId)
            ->setParameter('otherUser', $otherUserId)
            ->getQuery()
            ->getResult()
        ;
    }
    public function findConversationsByUser(int $userId)
    {
        $qb =$this->createQueryBuilder('c');
        return $qb
            ->select('otherUser.username', 'c.id as conversationId', 'lm.content', 'lm.createdAt')
            ->innerJoin('c.participants', 'p', Join::WITH, $qb->expr()->neq('p.user', ':user'))
            ->innerJoin('c.participants', 'me', Join::WITH, $qb->expr()->eq('me.user', ':user'))
            ->leftJoin('c.lastMessage', 'lm', Join::WITH, $qb->expr()->eq('me.user', ':user'))
            ->innerJoin('me.user', 'meUser')
            ->innerJoin('p.user', 'otherUser')
            ->where('me.user = :user')
            ->orderBy('lm.createdAt', 'DESC')
            ->setParameter(':user', $userId)
            ->getQuery()
            ->getResult()
        ;
    }
    public function checkIfUserIsParticipant(int $conversationId, int $userId)
    {
        $qb =$this->createQueryBuilder('c');
        return $qb
            ->select()
            ->innerJoin('c.participants', 'p')
            ->where('c.id = :conversationId')
            ->andWhere('p.user = :userId')
            ->setParameter(':userId', $userId)
            ->setParameter(':conversationId', $conversationId)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
