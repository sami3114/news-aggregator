<?php

namespace App\Services\Response;

use Illuminate\Http\JsonResponse;

class ResponseService
{
    /**
     * Return a success JSON response.
     *
     * @param string $message
     * @param mixed $data
     * @param int $statusCode
     * @return JsonResponse
     */
    public static function successResponse(string $message = 'Success', mixed $data = null, int $statusCode = 200): JsonResponse
    {
        $response = [
            'success' => true,
            'message' => $message,
        ];

        if (!is_null($data)) {
            $response['data'] = $data;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Return an error JSON response.
     *
     * @param string $message
     * @param mixed $data
     * @param int $statusCode
     * @return JsonResponse
     */
    public static function errorResponse(string $message = 'Error', mixed $data = null, int $statusCode = 400): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if (!is_null($data)) {
            $response['errors'] = $data;
        }

        return response()->json($response, $statusCode);
    }
}
