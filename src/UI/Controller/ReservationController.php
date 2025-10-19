<?php
namespace App\UI\Controller;


use App\Application\UseCase\{CreateReservation, ExpireReservations};
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;


class ReservationController
{
#[Route('/api/reservations', methods:['POST'])]
public function create(Request $r, CreateReservation $use): JsonResponse
{ $p = $r->toArray(); return new JsonResponse(($use)($p['memberId'],$p['bookId']), 201); }


#[Route('/api/reservations/expire/{bookId}', methods:['POST'])]
public function expire(string $bookId, ExpireReservations $use): JsonResponse
{ $use($bookId); return new JsonResponse(['status'=>'ok']); }
}
