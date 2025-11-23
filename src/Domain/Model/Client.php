<?php

namespace App\Domain\Model;

use Symfony\Component\Uid\Uuid;

final class Client
{
    private Uuid $id;
    private string $name;
    private string $email;
    private bool $active;

    public function __construct(Uuid $id, string $name, string $email)
    {
        $this->id     = $id;
        $this->name   = $name;
        $this->email  = $email;
        $this->active = true;
    }

    public function deactivate(): void
    {
        if (!$this->active) {
            throw new \DomainException('Client is already deactivated.');
        }

        $this->active = false;
    }

    public function activate(): void
    {
        if ($this->active) {
            throw new \DomainException('Client is already active.');
        }

        $this->active = true;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function id(): Uuid
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function email(): string
    {
        return $this->email;
    }
}
