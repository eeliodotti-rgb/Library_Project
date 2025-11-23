<?php

namespace App\Tests\Application\UseCase;

use App\Application\UseCase\GetClientDashboardUseCase;
use App\Application\UseCase\GetClientDashboardRequest;
use App\Application\UseCase\GetClientDashboardResponse;
use App\Domain\Model\Client;
use App\Domain\Model\Rental;
use App\Domain\Model\Book;
use App\Domain\Repository\ClientRepositoryInterface;
use App\Domain\Repository\RentalRepositoryInterface;
use App\Domain\Repository\BookRepositoryInterface;
use PHPUnit\Framework\TestCase;

final class GetClientDashboardUseCaseTest extends TestCase
{
    /**
     * 1. Client introuvable -> DomainException, aucun appel aux autres repo.
     */
    public function testExecuteWithUnknownClientThrowsDomainException(): void
    {
        $request = new GetClientDashboardRequest();
        $request->clientId = 'client-123';

        $clientRepository = $this->createMock(ClientRepositoryInterface::class);
        $rentalRepository = $this->createMock(RentalRepositoryInterface::class);
        $bookRepository   = $this->createMock(BookRepositoryInterface::class);

        $clientRepository
            ->expects($this->once())
            ->method('getById')
            ->with($request->clientId)
            ->willReturn(null);

        $rentalRepository
            ->expects($this->never())
            ->method('findActiveRentalsByClient');

        $bookRepository
            ->expects($this->never())
            ->method('getById');

        $useCase = new GetClientDashboardUseCase(
            $clientRepository,
            $rentalRepository,
            $bookRepository
        );

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Client not found.');

        $useCase->execute($request);
    }

    /**
     * 2. Client trouvé, aucun emprunt actif -> dashboard vide, aucune amende.
     */
    public function testExecuteWithNoActiveRentalsReturnsEmptyDashboard(): void
    {
        $request = new GetClientDashboardRequest();
        $request->clientId = 'client-123';

        $clientRepository = $this->createMock(ClientRepositoryInterface::class);
        $rentalRepository = $this->createMock(RentalRepositoryInterface::class);
        $bookRepository   = $this->createMock(BookRepositoryInterface::class);

        $client = $this->createMock(Client::class);
        $client
            ->method('id')
            ->willReturn('client-123');
        $client
            ->method('name')
            ->willReturn('Jean Dupont');
        $client
            ->method('email')
            ->willReturn('jean.dupont@example.com');

        $clientRepository
            ->expects($this->once())
            ->method('getById')
            ->with($request->clientId)
            ->willReturn($client);

        $rentalRepository
            ->expects($this->once())
            ->method('findActiveRentalsByClient')
            ->with('client-123')
            ->willReturn([]);

        $bookRepository
            ->expects($this->never())
            ->method('getById');

        $useCase = new GetClientDashboardUseCase(
            $clientRepository,
            $rentalRepository,
            $bookRepository
        );

        /** @var GetClientDashboardResponse $response */
        $response = $useCase->execute($request);

        // Adapte selon ton DTO (propriétés publiques vs getters)
        $this->assertSame('client-123', $response->clientId);
        $this->assertSame('Jean Dupont', $response->name);
        $this->assertSame('jean.dupont@example.com', $response->email);
        $this->assertSame([], $response->rentals);
        $this->assertSame(0, $response->totalOutstandingFines);
    }

    /**
     * 3. Client avec emprunts : 1 à l’heure, 1 en retard -> vérifie les données et le calcul d’amende.
     */
    public function testExecuteWithMixedRentalsComputesLateFeesAndRentalData(): void
    {
        $request = new GetClientDashboardRequest();
        $request->clientId = 'client-123';

        $clientRepository = $this->createMock(ClientRepositoryInterface::class);
        $rentalRepository = $this->createMock(RentalRepositoryInterface::class);
        $bookRepository   = $this->createMock(BookRepositoryInterface::class);

        // --- Client ---
        $client = $this->createMock(Client::class);
        $client
            ->method('id')
            ->willReturn('client-123');
        $client
            ->method('name')
            ->willReturn('Jean Dupont');
        $client
            ->method('email')
            ->willReturn('jean.dupont@example.com');

        $clientRepository
            ->method('getById')
            ->willReturn($client);

        // --- Rentals ---
        $now = new \DateTimeImmutable();

        $onTimeRental = $this->createMock(Rental::class);
        $onTimeRental
            ->method('id')
            ->willReturn('rental-on-time');
        $onTimeRental
            ->method('bookId')
            ->willReturn('book-1');
        $onTimeRental
            ->method('borrowedAt')
            ->willReturn($now->sub(new \DateInterval('P1D')));
        $onTimeRental
            ->method('dueDate')
            ->willReturn($now->add(new \DateInterval('P3D'))); // pas en retard

        $lateRental = $this->createMock(Rental::class);
        $lateRental
            ->method('id')
            ->willReturn('rental-late');
        $lateRental
            ->method('bookId')
            ->willReturn('book-2');
        $lateRental
            ->method('borrowedAt')
            ->willReturn($now->sub(new \DateInterval('P5D')));
        $lateRental
            ->method('dueDate')
            ->willReturn($now->sub(new \DateInterval('P2D'))); // 2 jours de retard

        $rentalRepository
            ->method('findActiveRentalsByClient')
            ->with('client-123')
            ->willReturn([$onTimeRental, $lateRental]);

        // --- Books correspondants ---
        $book1 = $this->createMock(Book::class);
        $book1
            ->method('id')
            ->willReturn('book-1');
        $book1
            ->method('title')
            ->willReturn('Clean Code');

        $book2 = $this->createMock(Book::class);
        $book2
            ->method('id')
            ->willReturn('book-2');
        $book2
            ->method('title')
            ->willReturn('Refactoring');

        $bookRepository
            ->method('getById')
            ->willReturnMap([
                ['book-1', $book1],
                ['book-2', $book2],
            ]);

        $useCase = new GetClientDashboardUseCase(
            $clientRepository,
            $rentalRepository,
            $bookRepository
        );

        /** @var GetClientDashboardResponse $response */
        $response = $useCase->execute($request);

        $this->assertCount(2, $response->rentals);

        // On grosso modo sait que 2 jours de retard -> 200 cents
        $this->assertSame(200, $response->totalOutstandingFines);

        // Vérif un peu plus fine
        $rental0 = $response->rentals[0];
        $this->assertSame('rental-on-time', $rental0['rentalId']);
        $this->assertSame('book-1', $rental0['bookId']);
        $this->assertSame('Clean Code', $rental0['title']);
        $this->assertFalse($rental0['isLate']);
        $this->assertSame(0, $rental0['lateFee']);

        $rental1 = $response->rentals[1];
        $this->assertSame('rental-late', $rental1['rentalId']);
        $this->assertSame('book-2', $rental1['bookId']);
        $this->assertSame('Refactoring', $rental1['title']);
        $this->assertTrue($rental1['isLate']);
        $this->assertSame(200, $rental1['lateFee']);
    }

    /**
     * 4. Un rental fait référence à un livre inexistant -> il est ignoré.
     */
    public function testExecuteSkipsRentalsWithMissingBook(): void
    {
        $request = new GetClientDashboardRequest();
        $request->clientId = 'client-123';

        $clientRepository = $this->createMock(ClientRepositoryInterface::class);
        $rentalRepository = $this->createMock(RentalRepositoryInterface::class);
        $bookRepository   = $this->createMock(BookRepositoryInterface::class);

        $client = $this->createMock(Client::class);
        $client
            ->method('id')
            ->willReturn('client-123');
        $client
            ->method('name')
            ->willReturn('Jean Dupont');
        $client
            ->method('email')
            ->willReturn('jean.dupont@example.com');

        $clientRepository
            ->method('getById')
            ->willReturn($client);

        $now = new \DateTimeImmutable();

        $rental = $this->createMock(Rental::class);
        $rental
            ->method('id')
            ->willReturn('rental-1');
        $rental
            ->method('bookId')
            ->willReturn('unknown-book');
        $rental
            ->method('borrowedAt')
            ->willReturn($now->sub(new \DateInterval('P1D')));
        $rental
            ->method('dueDate')
            ->willReturn($now->add(new \DateInterval('P2D')));

        $rentalRepository
            ->method('findActiveRentalsByClient')
            ->with('client-123')
            ->willReturn([$rental]);

        // getById retourne null -> livre introuvable
        $bookRepository
            ->method('getById')
            ->with('unknown-book')
            ->willReturn(null);

        $useCase = new GetClientDashboardUseCase(
            $clientRepository,
            $rentalRepository,
            $bookRepository
        );

        /** @var GetClientDashboardResponse $response */
        $response = $useCase->execute($request);

        $this->assertSame([], $response->rentals);
        $this->assertSame(0, $response->totalOutstandingFines);
    }
}
