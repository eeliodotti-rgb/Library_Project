<?php
use App\Domain\Book\CopyStatus;
use App\Infrastructure\Doctrine\Transactional;
use App\Infrastructure\Time\Clock;
use Ramsey\Uuid\Uuid;


final class BorrowBook
{
public function __construct(
private Transactional $tx,
private MemberRepository $members,
private CopyRepository $copies,
private LoanRepository $loans,
private ReservationRepository $reservations,
private Clock $clock
) {}


public function __invoke(string $memberId, string $bookId): array
{
return $this->tx->run(function() use ($memberId,$bookId){
$now = $this->clock->now();
$m = $this->members->find($memberId);
if (!$m) throw new \RuntimeException('member not found');
if ($m->isSuspended()) throw new MemberSuspended();
if ($this->loans->countActiveForMember($memberId) >= Policy::MAX_ACTIVE_LOANS_PER_MEMBER) throw new LoanLimitExceeded();
if ($this->loans->existsActiveForMemberAndBook($memberId,$bookId)) throw new \LogicException('Already holding book');


// Prefer reserved copy for this member
$copy = $this->copies->lockReservedCopyForBookAndMember($bookId,$memberId);
if ($copy) {
$copy->status = CopyStatus::ON_LOAN; $copy->reservedForMemberId = null;
// remove their reservation head if applicable
$this->reservations->popHeadAndReindex($bookId);
} else {
$copy = $this->copies->lockAvailableCopyForBook($bookId);
if (!$copy) throw new NoCopiesAvailable();
$copy->status = CopyStatus::ON_LOAN;
}


$loan = new Loan(
id: Uuid::uuid4()->toString(),
memberId: $memberId,
bookId: $bookId,
copyId: $copy->id,
loanedAt: $now,
dueAt: $now->modify('+' . Policy::STANDARD_LOAN_DAYS . ' days')
);
$this->loans->save($loan);
return ['loanId'=>$loan->id,'copyId'=>$copy->id,'dueAt'=>$loan->dueAt->format(DATE_ATOM)];
});
}
}
