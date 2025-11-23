<?php

namespace App\Application\UseCase;

final class GetClientDashboardResponse
{
    /** @param array<int, array<string,mixed>> $rentals */
    public function __construct(
        public string $clientId,
        public string $name,
        public string $email,
        public array $rentals,
        public int $totalOutstandingFines
    ) {}
}
