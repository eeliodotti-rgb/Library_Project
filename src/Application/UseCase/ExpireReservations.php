<?php
namespace App\Application\UseCase;


use App\Domain\Ports\{ReservationRepository, CopyRepository};
use App\Domain\Book\CopyStatus;
use App\Infrastructure\Doctrine\Transactional;
use App\Infrastructure\Time\Clock;


final class ExpireReservations
{
public function __construct(private Transactional $tx, private ReservationRepository $reservations, private CopyRepository $copies, private Clock $clock) {}


public function __invoke(string $bookId): void
{
$this->tx->run(function() use ($bookId){
$now = $this->clock->now();
$head = $this->reservations->findQueueHead($bookId);
if ($head && $head->expiresAt && $now > $head->expiresAt) {
$this->reservations->popHeadAndReindex($bookId);
// free a RESERVED copy to next or AVAILABLE
$copy = $this->copies->lockAvailableCopyForBook($bookId); // by now might be reserved; handle in repo impl
$this->copies->updateStatus($copy->id, CopyStatus::AVAILABLE);
}
});
}
}
