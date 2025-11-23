<?php

namespace App\Application\UseCase;

use Symfony\Component\Uid\Uuid;

final class GetClientDashboardRequest
{
    public function __construct(
        public Uuid $clientId
    ) {}
}
