<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

trait ApiResponder
{
    /**
     * Return a success JSON response.
     */
    protected function successResponse($data, string $message = '', int $statusCode = Response::HTTP_OK): JsonResponse
    {
        return response()->json([
            'data' => $data,
            'message' => $message
        ], $statusCode);
    }

    /**
     * Return a generic error JSON response.
     */
    protected function errorResponse(string $message, int $statusCode, ?array $errors = []): JsonResponse
    {
        $response = [
            'message' => $message,
        ];
        if (!empty($errors)) {
            $response['errors'] = $errors;
        }
        return response()->json($response, $statusCode);
    }
}
