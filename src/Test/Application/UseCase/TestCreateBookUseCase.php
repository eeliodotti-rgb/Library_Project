<?php

namespace App\Tests\Application\UseCase;

use App\Application\DTO\CreateBookRequest;
use App\Application\UseCase\CreateBookUseCase;
use App\Domain\Model\Book;
use App\Domain\Repository\BookRepositoryInterface;
use App\Domain\ValueObject\Money;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

final class CreateBookUseCaseTest extends TestCase
{
    /**
     * 1. Cas nominal : un livre valide est créé, sauvegardé, et un UUID est retourné.
     */
    public function testExecuteWithValidRequestSavesBookAndReturnsUuid(): void
    {
        // Arrange
        $request = new CreateBookRequest();
        $request->title         = 'Domain-Driven Design';
        $request->priceInCents  = 2999;
        $request->currency      = 'EUR';
        $request->isbn          = '978-0321125217'; // selon ton DTO

        $repository = $this->createMock(BookRepositoryInterface::class);

        // On s’assure que save() est bien appelé une fois avec un Book
        $repository
            ->expects($this->once())
            ->method('save')
            ->with($this->isInstanceOf(Book::class));

        $useCase = new CreateBookUseCase($repository);

        // Act
        $bookId = $useCase->execute($request);

        // Assert
        $this->assertInstanceOf(Uuid::class, $bookId);
        $this->assertTrue(Uuid::isValid((string) $bookId));
    }

    /**
     * 2. Titre vide : on doit lever une DomainException et ne pas appeler le repository.
     */
    public function testExecuteWithEmptyTitleThrowsDomainException(): void
    {
        // Arrange
        $request = new CreateBookRequest();
        $request->title         = '   '; // seulement des espaces
        $request->priceInCents  = 1000;
        $request->currency      = 'EUR';
        $request->isbn          = '978-0321125217';

        $repository = $this->createMock(BookRepositoryInterface::class);

        // Le repository ne doit jamais être appelé en cas d’erreur de validation
        $repository
            ->expects($this->never())
            ->method('save');

        $useCase = new CreateBookUseCase($repository);

        // Assert + Act (dans cet ordre avec PHPUnit)
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Book title cannot be empty.');

        $useCase->execute($request);
    }

    /**
     * 3. Vérifier que le Book passé au repository contient bien les infos attendues
     *    (titre, prix, devise, ISBN).
     */
    public function testExecuteBuildsBookWithExpectedData(): void
    {
        // Arrange
        $request = new CreateBookRequest();
        $request->title         = 'Clean Code';
        $request->priceInCents  = 4500;
        $request->currency      = 'EUR';
        $request->isbn          = '978-0132350884';

        $repository = $this->createMock(BookRepositoryInterface::class);

        $repository
            ->expects($this->once())
            ->method('save')
            ->with($this->callback(function (Book $book) use ($request) {
                // Vérifie les propriétés du Book
                $this->assertSame($request->title, $book->title());
                $this->assertSame($request->isbn, $book->isbn());

                /** @var Money $price */
                $price = $book->price();
                $this->assertSame($request->priceInCents, $price->amountInCents());
                $this->assertSame($request->currency, $price->currency());

                // Si tout est OK, on retourne true pour valider le callback
                return true;
            }));

        $useCase = new CreateBookUseCase($repository);

        // Act
        $useCase->execute($request);

        // (Pas d’assert supplémentaire : tout est dans le callback ci-dessus)
    }
}
