<?php

namespace App\Domain\Repository;

use App\Domain\Model\Client;
use Symfony\Component\Uid\Uuid;

interface ClientRepositoryInterface
{
    /**
     * Returns the client or null if not found.
     */
    public function getById(Uuid $id): ?Client;

    /**
     * Finds a client by email. Useful for login or dashboard access.
     */
    public function getByEmail(string $email): ?Client;

    /**
     * Saves or updates the client in the storage layer.
     */
    public function save(Client $client): void;

    /**
     * Deletes a client by ID (optional).
     */
    public function delete(Uuid $id): void;
}
