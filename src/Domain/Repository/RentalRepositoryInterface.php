<?php

declare(strict_types=1);

namespace App\Domain\Repository;

use App\Domain\Model\Rental;
use Symfony\Component\Uid\Uuid;

interface RentalRepositoryInterface
{
    /**
     * Returns the rental by ID or null if not found.
     */
    public function getById(Uuid $id): ?Rental;

    /**
     * Saves a rental (new or updated).
     */
    public function save(Rental $rental): void;

    /**
     * Counts active rentals for a given client.
     * Useful for enforcing max 5 books rule.
     */
    public function countActiveRentalsForClient(Uuid $clientId): int;

    /**
     * Finds an active rental for a client and a book.
     * Useful to prevent duplicate borrowing.
     */
    public function findActiveRental(Uuid $clientId, Uuid $bookId): ?Rental;

    /**
     * Returns all active rentals for a client.
     * Useful for client dashboard.
     */
    public function findActiveRentalsForClient(Uuid $clientId): array;
}
