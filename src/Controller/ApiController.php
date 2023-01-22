<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class ApiController
{
    #[Route('/api', methods: ['POST'])]
    public function apiCheck(): JsonResponse
    {
        return new JsonResponse(
            [
                'response' => 'ok'
            ]
        );
    }

    #[Route('/{path}', requirements: ['path' => 'api.*'], methods: ['GET'])]
    public function apiForbidden(): JsonResponse
    {
        return new JsonResponse(
            [
                'response' => 'not allowed'
            ]
        );
    }

    #[Route('/api/{path}', requirements: ['path' => '.*'], methods: ['POST'])]
    public function apiDefault(): JsonResponse
    {
        return new JsonResponse(
            [
                'response' => 'undefined'
            ]
        );
    }
}