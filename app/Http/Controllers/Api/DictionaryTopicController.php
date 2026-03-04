<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Firestore\DictionaryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DictionaryTopicController extends Controller
{
    public function __construct(private readonly DictionaryService $service)
    {
    }

    public function index(): JsonResponse
    {
        return response()->json(['data' => $this->service->listTopics()]);
    }

    public function show(string $topicCloudId): JsonResponse
    {
        $topic = $this->service->findTopic($topicCloudId);

        if (! $topic) {
            return response()->json(['message' => 'Dictionary topic not found.'], 404);
        }

        return response()->json(['data' => $topic]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'cloud_id' => ['required', 'string', 'max:120'],
            'name' => ['required', 'string', 'max:255'],
            'icon_url' => ['nullable', 'string', 'max:2048'],
            'order_index' => ['nullable', 'integer', 'min:0'],
        ]);

        $topic = $this->service->upsertTopic($validated['cloud_id'], $validated);

        return response()->json(['data' => $topic], 201);
    }

    public function update(Request $request, string $topicCloudId): JsonResponse
    {
        $existing = $this->service->findTopic($topicCloudId);

        if (! $existing) {
            return response()->json(['message' => 'Dictionary topic not found.'], 404);
        }

        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'icon_url' => ['sometimes', 'nullable', 'string', 'max:2048'],
            'order_index' => ['sometimes', 'integer', 'min:0'],
        ]);

        $topic = $this->service->upsertTopic($topicCloudId, [
            ...$existing,
            ...$validated,
        ]);

        return response()->json(['data' => $topic]);
    }

    public function destroy(string $topicCloudId): JsonResponse
    {
        if (! $this->service->deleteTopic($topicCloudId)) {
            return response()->json(['message' => 'Dictionary topic not found.'], 404);
        }

        return response()->json(status: 204);
    }
}
