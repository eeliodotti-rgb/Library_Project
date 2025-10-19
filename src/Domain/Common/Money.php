<?php
namespace App\Domain\Common;


final class Money
{
private int $cents;
private string $currency;


private function __construct(int $cents, string $currency)
{
if ($cents < 0) throw new \InvalidArgumentException('Money cannot be negative');
$this->cents = $cents; $this->currency = $currency;
}


public static function eurFromFloat(float $amount): self
{ return new self((int) round($amount * 100), 'EUR'); }


public static function fromCents(int $cents, string $currency='EUR'): self
{ return new self($cents, $currency); }


public function add(self $other): self
{ $this->assertSameCurrency($other); return new self($this->cents + $other->cents, $this->currency); }


public function subtract(self $other): self
{ $this->assertSameCurrency($other); if ($other->cents > $this->cents) throw new \InvalidArgumentException('insufficient'); return new self($this->cents - $other->cents, $this->currency); }


public function cents(): int { return $this->cents; }
public function currency(): string { return $this->currency; }
public function toFloat(): float { return $this->cents / 100.0; }


private function assertSameCurrency(self $other): void { if ($this->currency !== $other->currency) throw new \InvalidArgumentException('currency mismatch'); }
}
