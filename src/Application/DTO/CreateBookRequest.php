<?php

namespace App\Application\DTO;

final class CreateBookRequest
{
    public function __construct(
        public string $title,
        public string $isbn,
        public int $priceInCents,
        public string $currency
    ) {}
}
<?php
