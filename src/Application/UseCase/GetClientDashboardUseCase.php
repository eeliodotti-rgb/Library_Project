<?php

namespace App\Application\UseCase;

use App\Domain\Repository\ClientRepositoryInterface;
use App\Domain\Repository\RentalRepositoryInterface;
use App\Domain\Repository\BookRepositoryInterface;

final class GetClientDashboardUseCase
{
    public function __construct(
        private ClientRepositoryInterface $clientRepository,
        private RentalRepositoryInterface $rentalRepository,
        private BookRepositoryInterface $bookRepository
    ) {}

    public function execute(GetClientDashboardRequest $request): GetClientDashboardResponse
    {
        // 1. Load client
        $client = $this->clientRepository->getById($request->clientId);
        if ($client === null) {
            throw new \DomainException('Client not found.');
        }

        // 2. Load active rentals for the client
        $rentals = $this->rentalRepository->findActiveRentalsByClient($client->id());

        $rentalData = [];
        $totalFines = 0;

        $now = new \DateTimeImmutable();

        // 3. For each rental, enrich with book info and fines
        foreach ($rentals as $rental) {
            $book = $this->bookRepository->getById($rental->bookId());

            if ($book === null) {
                // Should never happen — but safe handling
                continue;
            }

            $isLate = $rental->dueDate() < $now;

            $lateFee = 0;
            if ($isLate) {
                // Here: simple rule, e.g. 1€ per day late
                $daysLate = $rental->dueDate()->diff($now)->days;
                $lateFee = $daysLate * 100; // in cents
            }

            $totalFines += $lateFee;

            $rentalData[] = [
                'rentalId'  => (string) $rental->id(),
                'bookId'    => (string) $book->id(),
                'title'     => $book->title(),
                'borrowedAt'=> $rental->borrowedAt()->format('Y-m-d'),
                'dueDate'   => $rental->dueDate()->format('Y-m-d'),
                'isLate'    => $isLate,
                'lateFee'   => $lateFee,
            ];
        }

        // 4. Build and return a response DTO
        return new GetClientDashboardResponse(
            clientId: (string) $client->id(),
            name: $client->name(),
            email: $client->email(),
            rentals: $rentalData,
            totalOutstandingFines: $totalFines
        );
    }
}
