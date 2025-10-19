<?php
namespace App\UI\Controller;


use App\Application\UseCase\PayFine;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;


class FineController
{
#[Route('/api/fines/pay', methods:['POST'])]
public function pay(Request $r, PayFine $use): JsonResponse
{ $p = $r->toArray(); return new JsonResponse(($use)($p['memberId'],$p['amountCents'],$p['loanId'] ?? null)); }
}
