<?php
namespace App\Application\UseCase;


use App\Domain\Ports\{LoanRepository,ReservationRepository,CopyRepository,MemberRepository};
use App\Domain\Book\CopyStatus;
use App\Infrastructure\Doctrine\Transactional;
use App\Infrastructure\Time\Clock;


final class ReturnBook
{
public function __construct(private Transactional $tx, private LoanRepository $loans, private ReservationRepository $reservations, private CopyRepository $copies, private MemberRepository $members, private Clock $clock) {}


public function __invoke(string $loanId): array
{
return $this->tx->run(function() use ($loanId){
$now = $this->clock->now();
$loan = $this->loans->findActiveById($loanId);
if (!$loan) throw new \RuntimeException('loan not found');
$fine = $loan->returnAndCalculateFine($now);
$this->loans->save($loan);


// If queue exists, reserve for head
if ($this->reservations->countActiveForBook($loan->bookId) > 0) {
$head = $this->reservations->findQueueHead($loan->bookId);
$copy = $this->copies->lockAvailableCopyForBook($loan->bookId) ?? (object)['id'=>$loan->copyId];
$this->copies->updateStatus($copy->id, CopyStatus::RESERVED);
// mark reservedForMemberId on copy entity
// (done via updateStatus in real repo or by fetching entity)
$head && $this->activateHold($head);
} else {
$this->copies->updateStatus($loan->copyId, CopyStatus::AVAILABLE);
}


// apply fine to member
$m = $this->members->find($loan->memberId);
$m->applyUnpaidChange($fine->cents());
$this->members->save($m);


return ['fineCents'=>$fine->cents(),'memberStatus'=>$m->status->name];
});
}


private function activateHold($reservation): void
{ $reservation->activateHold($this->clock->now()); }
}
