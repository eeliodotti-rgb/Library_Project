<?php
namespace App\Domain\Lending;


final class Policy
{
public const MAX_ACTIVE_LOANS_PER_MEMBER = 5;
public const STANDARD_LOAN_DAYS = 14;
public const MAX_RENEWALS = 2;
public const RENEWAL_DAYS = 14;
public const FINE_PER_DAY_EUR_CENTS = 50; // 0.50 EUR
public const RESERVATION_HOLD_HOURS = 48;
public const SUSPEND_IF_UNPAID_GT_EUR_CENTS = 2000; // 20 EUR
}
