<?php
namespace App\Domain\Lending;


use App\Domain\Common\Money;


class Loan
{
public function __construct(
public readonly string $id,
public readonly string $memberId,
public readonly string $bookId,
public readonly string $copyId,
public readonly \DateTimeImmutable $loanedAt,
public \DateTimeImmutable $dueAt,
public ?\DateTimeImmutable $returnedAt = null,
public int $renewalCount = 0,
public Money $fineAccrued = new Money(0,'EUR')
) {}


public function isActive(): bool { return $this->returnedAt === null; }
public function isOverdue(\DateTimeImmutable $now): bool { return $this->returnedAt === null && $now > $this->dueAt; }


public function renew(\DateTimeImmutable $now): void
{
if ($this->isOverdue($now)) throw new LoanOverdue('Cannot renew overdue loan');
if ($this->renewalCount >= Policy::MAX_RENEWALS) throw new MaxRenewalsReached();
$this->dueAt = $this->dueAt->modify('+' . Policy::RENEWAL_DAYS . ' days');
$this->renewalCount++;
}


public function returnAndCalculateFine(\DateTimeImmutable $now): Money
{
$this->returnedAt = $now;
if ($now <= $this->dueAt) return Money::fromCents(0);
$daysLate = (int) $this->dueAt->diff($now)->format('%a');
$fine = Money::fromCents($daysLate * Policy::FINE_PER_DAY_EUR_CENTS);
$this->fineAccrued = $this->fineAccrued->add($fine);
return $fine;
}
}
