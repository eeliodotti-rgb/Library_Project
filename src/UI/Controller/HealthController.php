<?php
namespace App\UI\Controller;


use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;


class HealthController
{ #[Route('/healthz', methods:['GET'])] public function __invoke(): JsonResponse { return new JsonResponse(['status'=>'ok']); } }
