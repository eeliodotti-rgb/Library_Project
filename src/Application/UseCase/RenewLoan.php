<?php
namespace App\Application\UseCase;


use App\Domain\Ports\{LoanRepository,ReservationRepository,MemberRepository};
use App\Domain\Lending\{LoanOverdue, MaxRenewalsReached};
use App\Infrastructure\Doctrine\Transactional;
use App\Infrastructure\Time\Clock;


final class RenewLoan
{
public function __construct(private Transactional $tx, private LoanRepository $loans, private ReservationRepository $reservations, private MemberRepository $members, private Clock $clock) {}
public function __invoke(string $loanId): array
{
return $this->tx->run(function() use ($loanId){
$now = $this->clock->now();
$loan = $this->loans->findActiveById($loanId);
$member = $this->members->find($loan->memberId);
if ($member->isSuspended()) throw new \DomainException('Member suspended');
if ($this->reservations->countActiveForBook($loan->bookId) > 0) throw new \DomainException('Reservations exist');
$loan->renew($now); // throws overdue/max
$this->loans->save($loan);
return ['dueAt'=>$loan->dueAt->format(DATE_ATOM),'renewalCount'=>$loan->renewalCount];
});
}
}
