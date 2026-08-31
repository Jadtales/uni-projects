<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApiError
{
    public static function make(Request $request, int $status, string $message): JsonResponse
    {
        return response()->json([
            'error' => [
                'status' => $status,
                'message' => $message,
                'path' => $request->getRequestUri(),
            ],
        ], $status);
    }
}
