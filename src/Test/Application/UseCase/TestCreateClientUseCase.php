<?php

namespace App\Tests\Application\UseCase;

use App\Application\DTO\CreateClientRequest;
use App\Application\UseCase\CreateClientUseCase;
use App\Domain\Model\Client;
use App\Domain\Repository\ClientRepositoryInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

final class CreateClientUseCaseTest extends TestCase
{
    /**
     * 1. Cas nominal : client valide, sauvegardé, UUID retourné.
     */
    public function testExecuteWithValidRequestSavesClientAndReturnsUuid(): void
    {
        // Arrange
        $request = new CreateClientRequest();
        $request->name  = 'Jean Dupont';
        $request->email = 'jean.dupont@example.com';

        $repository = $this->createMock(ClientRepositoryInterface::class);

        $repository
            ->expects($this->once())
            ->method('save')
            ->with($this->isInstanceOf(Client::class));

        $useCase = new CreateClientUseCase($repository);

        // Act
        $clientId = $useCase->execute($request);

        // Assert
        $this->assertInstanceOf(Uuid::class, $clientId);
        $this->assertTrue(Uuid::isValid((string) $clientId));
    }

    /**
     * 2. Nom vide : on lève une DomainException et save() n’est pas appelé.
     */
    public function testExecuteWithEmptyNameThrowsDomainExceptionAndDoesNotSave(): void
    {
        // Arrange
        $request = new CreateClientRequest();
        $request->name  = '   '; // espaces uniquement
        $request->email = 'jean.dupont@example.com';

        $repository = $this->createMock(ClientRepositoryInterface::class);

        $repository
            ->expects($this->never())
            ->method('save');

        $useCase = new CreateClientUseCase($repository);

        // Assert + Act
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Client name cannot be empty.');

        $useCase->execute($request);
    }

    /**
     * 3. Email invalide : DomainException, repository non appelé.
     */
    public function testExecuteWithInvalidEmailThrowsDomainExceptionAndDoesNotSave(): void
    {
        // Arrange
        $request = new CreateClientRequest();
        $request->name  = 'Jean Dupont';
        $request->email = 'not-an-email';

        $repository = $this->createMock(ClientRepositoryInterface::class);

        $repository
            ->expects($this->never())
            ->method('save');

        $useCase = new CreateClientUseCase($repository);

        // Assert + Act
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Invalid email format.');

        $useCase->execute($request);
    }

    /**
     * 4. Vérifier que l’entité Client construite contient bien les infos attendues.
     */
    public function testExecuteBuildsClientWithExpectedData(): void
    {
        // Arrange
        $request = new CreateClientRequest();
        $request->name  = 'Marie Curie';
        $request->email = 'marie.curie@example.com';

        $repository = $this->createMock(ClientRepositoryInterface::class);

        $repository
            ->expects($this->once())
            ->method('save')
            ->with($this->callback(function (Client $client) use ($request) {
                // Vérifie les propriétés du client
                $this->assertInstanceOf(Uuid::class, $client->id());
                $this->assertSame($request->name, $client->name());
                $this->assertSame($request->email, $client->email());

                return true;
            }));

        $useCase = new CreateClientUseCase($repository);

        // Act
        $useCase->execute($request);
    }

    /**
     * 5. Nom avec espaces autour : trim() doit le considérer comme non vide et passer.
     * (Ce test valide surtout l’utilisation de trim() dans le use case.)
     */
    public function testExecuteTrimsNameBeforeValidation(): void
    {
        // Arrange
        $request = new CreateClientRequest();
        $request->name  = '   Jean Dupont   ';
        $request->email = 'jean.dupont@example.com';

        $repository = $this->createMock(ClientRepositoryInterface::class);

        $repository
            ->expects($this->once())
            ->method('save')
            ->with($this->callback(function (Client $client) {
                // On s’assure que le nom a bien été conservé « tel quel » dans l’entité
                // (si ta logique métier veut autre chose, adapte ce test).
                $this->assertSame('   Jean Dupont   ', $client->name());
                return true;
            }));

        $useCase = new CreateClientUseCase($repository);

        // Act & Assert : aucune exception ne doit être levée
        $useCase->execute($request);

        $this->assertTrue(true); // Juste pour expliciter qu’on est passé ici
    }
}
