<?php
use App\Domain\Book\CopyStatus;
use App\Domain\Lending\{Loan, Reservation};
use App\Domain\Member\Member;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\DBAL\LockMode;
use Ramsey\Uuid\Uuid;


class DoctrineRepositories implements CopyRepository, ReservationRepository, LoanRepository, MemberRepository, BookRepository, FinePaymentRepository
{
public function __construct(private EntityManagerInterface $em) {}


public function lockAvailableCopyForBook(string $bookId): ?object
{
$qb = $this->em->createQueryBuilder()
->select('c')
->from('App\\Infrastructure\\Doctrine\\Entity\\CopyEntity','c')
->where('c.bookId = :b AND c.status = :s')
->setParameter('b',$bookId)->setParameter('s', CopyStatus::AVAILABLE->value)
->setMaxResults(1);
$copy = $qb->getQuery()->setLockMode(LockMode::PESSIMISTIC_WRITE)->getOneOrNullResult();
return $copy; // mapped entity; caller updates status
}


public function lockReservedCopyForBookAndMember(string $bookId, string $memberId): ?object
{
$qb = $this->em->createQueryBuilder()
->select('c')
->from('App\\Infrastructure\\Doctrine\\Entity\\CopyEntity','c')
->where('c.bookId = :b AND c.status = :s AND c.reservedForMemberId = :m')
->setParameters(['b'=>$bookId,'s'=>CopyStatus::RESERVED->value,'m'=>$memberId])
->setMaxResults(1);
return $qb->getQuery()->setLockMode(LockMode::PESSIMISTIC_WRITE)->getOneOrNullResult();
}


public function findQueueHead(string $bookId): ?Reservation
{
$r = $this->em->createQuery('SELECT r FROM App\\Infrastructure\\Doctrine\\Entity\\ReservationEntity r WHERE r.bookId = :b ORDER BY r.position ASC')
->setParameter('b',$bookId)->setMaxResults(1)
->setLockMode(LockMode::PESSIMISTIC_WRITE)
->getOneOrNullResult();
return $r?->toDomain();
}


public function popHeadAndReindex(string $bookId): ?Reservation
{
$head = $this->findQueueHead($bookId);
if (!$head) return null;
$this->em->createQuery('DELETE FROM App\\Infrastructure\\Doctrine\\Entity\\ReservationEntity r WHERE r.id = :id')
->setParameter('id',$head->id)->execute();
// reindex positions
$items = $this->em->createQuery('SELECT r FROM App\\Infrastructure\\Doctrine\\Entity\\ReservationEntity r WHERE r.bookId = :b ORDER BY r.position ASC')
->setParameter('b',$bookId)->getResult();
$pos=1; foreach ($items as $e){ $e->position=$pos++; }
return $head;
}


// ... Member, Loan save/find elided for brevity
}
