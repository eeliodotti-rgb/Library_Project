<?php
namespace App\Domain\Lending;


class Reservation
{
public function __construct(
public readonly string $id,
public readonly string $bookId,
public readonly string $memberId,
public int $position,
public readonly \DateTimeImmutable $createdAt,
public ?\DateTimeImmutable $expiresAt = null
) {}


public function activateHold(\DateTimeImmutable $now): void
{ $this->expiresAt = $now->modify('+' . Policy::RESERVATION_HOLD_HOURS . ' hours'); }


public function isActiveHold(\DateTimeImmutable $now): bool
{ return $this->expiresAt !== null && $now <= $this->expiresAt; }
}
