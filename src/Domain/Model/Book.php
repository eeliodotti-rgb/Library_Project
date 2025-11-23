<?php
namespace App\Domain\Model;

use Symfony\Component\Uid\Uuid;

final class Book
{
    private Uuid $id;
    private string $title;
    private string $isbn;
    private bool $isAvailable;

    public function __construct(Uuid $id, string $title, string $isbn)
    {
        $this->id = $id;
        $this->title = $title;
        $this->isbn = $isbn;
        $this->isAvailable = true;
    }

    public function borrow(): void
    {
        if (!$this->isAvailable) {
            throw new \DomainException('This book is already borrowed.');
        }

        $this->isAvailable = false;
    }

    public function return(): void
    {
        if ($this->isAvailable) {
            throw new \DomainException("This book isn't currently borrowed.");
        }

        $this->isAvailable = true;
    }

    public function id(): Uuid
    {
        return $this->id;
    }

    public function title(): string
    {
        return $this->title;
    }

    public function isbn(): string
    {
        return $this->isbn;
    }

    public function isAvailable(): bool
    {
        return $this->isAvailable;
    }
}
