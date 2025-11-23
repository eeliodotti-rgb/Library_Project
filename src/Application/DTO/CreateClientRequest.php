<?php

namespace App\Application\DTO;

final class CreateClientRequest
{
    public function __construct(
        public string $name,
        public string $email
    ) {}
}
