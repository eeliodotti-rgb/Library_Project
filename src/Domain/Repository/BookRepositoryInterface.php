<?php

namespace App\Domain\Repository;

use App\Domain\Model\Book;
use Symfony\Component\Uid\Uuid;

interface BookRepositoryInterface
{
    /**
     * Returns the book by its ID or null if not found.
     */
    public function getById(Uuid $id): ?Book;

    /**
     * Returns the book if it is available, otherwise null.
     */
    public function findAvailableById(Uuid $id): ?Book;

    /**
     * Saves or updates the book in storage.
     */
    public function save(Book $book): void;

    /**
     * Deletes a book by ID (optional)
     */
    public function delete(Uuid $id): void;
}
