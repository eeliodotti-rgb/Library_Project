<?php

namespace App\Application\UseCase;

use App\Application\DTO\CreateClientRequest;
use App\Domain\Model\Client;
use App\Domain\Repository\ClientRepositoryInterface;
use Symfony\Component\Uid\Uuid;

final class CreateClientUseCase
{
    public function __construct(
        private ClientRepositoryInterface $clientRepository
    ) {}

    public function execute(CreateClientRequest $request): Uuid
    {
        // 1. Validate name
        if (trim($request->name) === '') {
            throw new \DomainException('Client name cannot be empty.');
        }

        // 2. Validate email
        if (!filter_var($request->email, FILTER_VALIDATE_EMAIL)) {
            throw new \DomainException('Invalid email format.');
        }

        // 3. Create the Client domain entity
        $client = new Client(
            Uuid::v4(),
            $request->name,
            $request->email
        );

        // 4. Save using the repository
        $this->clientRepository->save($client);

        // 5. Return its ID
        return $client->id();
    }
}
