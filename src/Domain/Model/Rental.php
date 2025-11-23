<?php

namespace App\Domain\Model;

use App\Domain\ValueObject\Money;
use Symfony\Component\Uid\Uuid;

final class Rental
{
    private Uuid $id;
    private Uuid $clientId;
    private Uuid $bookId;

    private \DateTimeImmutable $borrowedAt;
    private \DateTimeImmutable $dueDate;
    private ?\DateTimeImmutable $returnedAt = null;

    public function __construct(
        Uuid $id,
        Uuid $clientId,
        Uuid $bookId,
        \DateTimeImmutable $borrowedAt,
        \DateTimeImmutable $dueDate
    ) {
        $this->id = $id;
        $this->clientId = $clientId;
        $this->bookId = $bookId;
        $this->borrowedAt = $borrowedAt;
        $this->dueDate = $dueDate;
    }

    public function markReturned(\DateTimeImmutable $returnedAt): void
    {
        if ($this->returnedAt !== null) {
            throw new \DomainException('Rental already returned.');
        }

        $this->returnedAt = $returnedAt;
    }

    public function isActive(): bool
    {
        return $this->returnedAt === null;
    }

    public function isLate(\DateTimeImmutable $now = new \DateTimeImmutable()): bool
    {
        return $this->isActive() && $now > $this->dueDate;
    }

    public function id(): Uuid
    {
        return $this->id;
    }

    public function clientId(): Uuid
    {
        return $this->clientId;
    }

    public function bookId(): Uuid
    {
        return $this->bookId;
    }

    public function borrowedAt(): \DateTimeImmutable
    {
        return $this->borrowedAt;
    }

    public function dueDate(): \DateTimeImmutable
    {
        return $this->dueDate;
    }

    public function returnedAt(): ?\DateTimeImmutable
    {
        return $this->returnedAt;
    }
}
