<?php

namespace App\Application\UseCase;

use App\Application\DTO\CreateBookRequest;
use App\Domain\Model\Book;
use App\Domain\Repository\BookRepositoryInterface;
use App\Domain\ValueObject\Money;
use Symfony\Component\Uid\Uuid;

final class CreateBookUseCase
{
    public function __construct(
        private BookRepositoryInterface $bookRepository
    ) {}

    public function execute(CreateBookRequest $request): Uuid
    {
        // 1. Validate the title
        if (trim($request->title) === '') {
            throw new \DomainException('Book title cannot be empty.');
        }

        // 2. Create the Money value object
        $price = Money::fromInt($request->priceInCents, $request->currency);

        // 3. Create the Book domain entity
        $book = new Book(
            Uuid::v4(),
            $request->title,
            $isbn,
            $price
        );

        // 4. Save it using the repository
        $this->bookRepository->save($book);

        // 5. Return new book ID
        return $book->id();
    }
}
<?php
