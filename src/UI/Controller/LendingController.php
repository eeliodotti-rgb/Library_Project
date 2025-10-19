<?php
namespace App\UI\Controller;


use App\Application\UseCase\{BorrowBook, ReturnBook, RenewLoan};
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;


class LendingController
{
#[Route('/api/borrow', methods:['POST'])]
public function borrow(Request $r, BorrowBook $use): JsonResponse
{ $p = $r->toArray(); return new JsonResponse(($use)($p['memberId'],$p['bookId']), 201); }


#[Route('/api/return/{loanId}', methods:['POST'])]
public function return(string $loanId, ReturnBook $use): JsonResponse
{ return new JsonResponse(($use)($loanId)); }


#[Route('/api/renew/{loanId}', methods:['POST'])]
public function renew(string $loanId, RenewLoan $use): JsonResponse
{ return new JsonResponse(($use)($loanId)); }
}
