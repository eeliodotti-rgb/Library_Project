<?php
namespace App\Domain\Member;


class Member
{
public function __construct(
public readonly string $id,
public string $name,
public string $email,
public MemberStatus $status = MemberStatus::ACTIVE,
public int $unpaidCents = 0
) {}


public function applyUnpaidChange(int $deltaCents): void
{
$this->unpaidCents = max(0, $this->unpaidCents + $deltaCents);
$this->recalculateSuspension();
}


public function recalculateSuspension(): void
{
$this->status = ($this->unpaidCents > \App\Domain\Lending\Policy::SUSPEND_IF_UNPAID_GT_EUR_CENTS)
? MemberStatus::SUSPENDED : MemberStatus::ACTIVE;
}


public function isSuspended(): bool { return $this->status === MemberStatus::SUSPENDED; }
}
