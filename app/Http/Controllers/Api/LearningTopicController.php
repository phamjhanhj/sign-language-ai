<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Firestore\LearningTopicService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LearningTopicController extends Controller
{
    public function __construct(private readonly LearningTopicService $service)
    {
    }

    public function index(): JsonResponse
    {
        return response()->json(['data' => $this->service->list()]);
    }

    public function show(string $topicCloudId): JsonResponse
    {
        $topic = $this->service->find($topicCloudId);

        if (! $topic) {
            return response()->json(['message' => 'Learning topic not found.'], 404);
        }

        return response()->json(['data' => $topic]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'cloud_id' => ['required', 'string', 'max:120'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'thumbnail_url' => ['nullable', 'string', 'max:2048'],
            'order_index' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $topic = $this->service->upsert($validated['cloud_id'], $validated);

        return response()->json(['data' => $topic], 201);
    }

    public function update(Request $request, string $topicCloudId): JsonResponse
    {
        if (! $this->service->find($topicCloudId)) {
            return response()->json(['message' => 'Learning topic not found.'], 404);
        }

        $validated = $request->validate([
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
            'thumbnail_url' => ['sometimes', 'nullable', 'string', 'max:2048'],
            'order_index' => ['sometimes', 'integer', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $topic = $this->service->upsert($topicCloudId, [
            ...$this->service->find($topicCloudId),
            ...$validated,
        ]);

        return response()->json(['data' => $topic]);
    }

    public function destroy(string $topicCloudId): JsonResponse
    {
        if (! $this->service->delete($topicCloudId)) {
            return response()->json(['message' => 'Learning topic not found.'], 404);
        }

        return response()->json(status: 204);
    }

    public function generateLessons(Request $request, string $topicCloudId): JsonResponse
    {
        if (! $this->service->find($topicCloudId)) {
            return response()->json(['message' => 'Learning topic not found.'], 404);
        }

        $validated = $request->validate([
            'vocabularies' => ['required', 'array', 'min:4'],
            'vocabularies.*.cloud_id' => ['required', 'string', 'max:120'],
            'vocabularies.*.word' => ['required', 'string', 'max:255'],
            'vocabularies.*.video_url' => ['nullable', 'string', 'max:2048'],
            'vocabularies.*.explanation' => ['nullable', 'string'],
            'vocabularies.*.definition' => ['nullable', 'string'],

            'sentences' => ['required', 'array', 'min:4'],
            'sentences.*.text' => ['required', 'string'],
            'sentences.*.video_url' => ['nullable', 'string', 'max:2048'],
            'sentences.*.tokens' => ['nullable', 'array', 'min:2'],
            'sentences.*.tokens.*' => ['string', 'max:80'],
            'sentences.*.related_vocab_ids' => ['nullable', 'array'],
            'sentences.*.related_vocab_ids.*' => ['string', 'max:120'],
        ]);

        $result = $this->service->generateLessons(
            $topicCloudId,
            $validated['vocabularies'],
            $validated['sentences']
        );

        if (! ($result['ok'] ?? false)) {
            return response()->json(['message' => $result['message'] ?? 'Generate failed.'], 422);
        }

        return response()->json($result, 201);
    }
}
