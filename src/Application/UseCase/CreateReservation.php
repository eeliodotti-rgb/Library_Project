<?php
namespace App\Application\UseCase;


use App\Domain\Ports\{ReservationRepository,LoanRepository,MemberRepository,CopyRepository};
use App\Domain\Lending\{ReservationExists, AlreadyHoldingBook};
use App\Domain\Book\CopyStatus;
use App\Infrastructure\Doctrine\Transactional;
use App\Infrastructure\Time\Clock;
use Ramsey\Uuid\Uuid;


final class CreateReservation
{
public function __construct(private Transactional $tx, private ReservationRepository $reservations, private LoanRepository $loans, private MemberRepository $members, private CopyRepository $copies, private Clock $clock) {}


public function __invoke(string $memberId, string $bookId): array
{
return $this->tx->run(function() use ($memberId,$bookId){
$m = $this->members->find($memberId);
if ($m->isSuspended()) throw new \DomainException('Member suspended');
if ($this->loans->existsActiveForMemberAndBook($memberId,$bookId)) throw new AlreadyHoldingBook();
if ($this->reservations->existsForMemberAndBook($memberId,$bookId)) throw new ReservationExists();
// only if no available copies
$available = $this->copies->lockAvailableCopyForBook($bookId);
if ($available) throw new \DomainException('Copies available');


$pos = $this->reservations->countActiveForBook($bookId) + 1;
$res = new \App\Domain\Lending\Reservation(Uuid::uuid4()->toString(), $bookId, $memberId, $pos, $this->clock->now());
$this->reservations->save($res);
return ['reservationId'=>$res->id,'position'=>$res->position];
});
}
}
