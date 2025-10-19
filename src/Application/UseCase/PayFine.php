<?php
namespace App\Application\UseCase;


use App\Domain\Ports\{MemberRepository,FinePaymentRepository};
use App\Infrastructure\Doctrine\Transactional;
use App\Infrastructure\Time\Clock;
use Ramsey\Uuid\Uuid;


final class PayFine
{
public function __construct(private Transactional $tx, private MemberRepository $members, private FinePaymentRepository $payments, private Clock $clock) {}


public function __invoke(string $memberId, int $amountCents, ?string $loanId=null): array
{
return $this->tx->run(function() use ($memberId,$amountCents,$loanId){
$m = $this->members->find($memberId);
$amount = min($amountCents, $m->unpaidCents);
$this->payments->record(Uuid::uuid4()->toString(), $memberId, $loanId, $amount, $this->clock->now());
$m->applyUnpaidChange(-$amount);
$this->members->save($m);
return ['paidCents'=>$amount,'remainingCents'=>$m->unpaidCents,'memberStatus'=>$m->status->name];
});
}
}
