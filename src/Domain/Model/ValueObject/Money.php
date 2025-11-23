<?php

namespace App\Domain\ValueObject;

final class Money
{
    private int $amount; // stored in cents
    private string $currency;

    public function __construct(int $amount, string $currency = 'EUR')
    {
        if ($amount < 0) {
            throw new \DomainException("Money amount cannot be negative.");
        }

        $this->amount = $amount;
        $this->currency = $currency;
    }

    public static function fromFloat(float $amount, string $currency = 'EUR'): self
    {
        return new self((int) round($amount * 100), $currency);
    }

    public static function zero(string $currency = 'EUR'): self
    {
        return new self(0, $currency);
    }

    public function amount(): int
    {
        return $this->amount;
    }

    public function currency(): string
    {
        return $this->currency;
    }

    public function toFloat(): float
    {
        return $this->amount / 100;
    }

    public function add(Money $other): Money
    {
        $this->ensureSameCurrency($other);
        return new self($this->amount + $other->amount, $this->currency);
    }

    public function subtract(Money $other): Money
    {
        $this->ensureSameCurrency($other);

        if ($other->amount > $this->amount) {
            throw new \DomainException("Cannot subtract more money than available.");
        }

        return new self($this->amount - $other->amount, $this->currency);
    }

    private function ensureSameCurrency(Money $other): void
    {
        if ($this->currency !== $other->currency) {
            throw new \DomainException("Money currency mismatch.");
        }
    }

    public function __toString(): string
    {
        return number_format($this->toFloat(), 2) . ' ' . $this->currency;
    }
}
