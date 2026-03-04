<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Firestore\ContentPublishService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminContentController extends Controller
{
    public function __construct(private readonly ContentPublishService $service)
    {
    }

    public function publish(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'notes' => ['nullable', 'string'],
            'published_by' => ['nullable', 'string', 'max:255'],
        ]);

        $result = $this->service->publish(
            notes: $validated['notes'] ?? null,
            publishedBy: $validated['published_by'] ?? null,
        );

        return response()->json([
            'message' => 'Content published successfully.',
            'data' => $result,
        ], 201);
    }
}
