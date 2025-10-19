<?php
use PHPUnit\Framework\TestCase;
use App\Domain\Lending\{Loan, Policy, MaxRenewalsReached, LoanOverdue};


final class LoanPolicyTest extends TestCase
{
public function test_renew_increments_due_and_count(): void
{
$now = new DateTimeImmutable('2025-01-01');
$loan = new Loan('L','M','B','C',$now,$now->modify('+14 days'));
$loan->renew($now);
$this->assertEquals($now->modify('+28 days'), $loan->dueAt);
$this->assertSame(1, $loan->renewalCount);
}


public function test_cannot_renew_overdue(): void
{
$loan = new Loan('L','M','B','C',new DateTimeImmutable('-20 days'), new DateTimeImmutable('-6 days'));
$this->expectException(LoanOverdue::class);
$loan->renew(new DateTimeImmutable());
}
}
