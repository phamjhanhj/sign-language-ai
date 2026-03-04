<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Firestore\DictionaryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DictionaryVocabularyController extends Controller
{
    public function __construct(private readonly DictionaryService $service)
    {
    }

    public function index(string $topicCloudId): JsonResponse
    {
        if (! $this->service->findTopic($topicCloudId)) {
            return response()->json(['message' => 'Dictionary topic not found.'], 404);
        }

        return response()->json([
            'data' => $this->service->listVocabularies($topicCloudId),
        ]);
    }

    public function show(string $topicCloudId, string $vocabCloudId): JsonResponse
    {
        $vocabulary = $this->service->findVocabulary($topicCloudId, $vocabCloudId);

        if (! $vocabulary) {
            return response()->json(['message' => 'Vocabulary not found.'], 404);
        }

        return response()->json(['data' => $vocabulary]);
    }

    public function store(Request $request, string $topicCloudId): JsonResponse
    {
        if (! $this->service->findTopic($topicCloudId)) {
            return response()->json(['message' => 'Dictionary topic not found.'], 404);
        }

        $validated = $request->validate([
            'cloud_id' => ['required', 'string', 'max:120'],
            'word' => ['required', 'string', 'max:255'],
            'video_url' => ['required', 'string', 'max:2048'],
            'definition' => ['nullable', 'string'],
            'image_preview' => ['nullable', 'string', 'max:2048'],
        ]);

        $vocabulary = $this->service->upsertVocabulary(
            $topicCloudId,
            $validated['cloud_id'],
            $validated
        );

        return response()->json(['data' => $vocabulary], 201);
    }

    public function update(Request $request, string $topicCloudId, string $vocabCloudId): JsonResponse
    {
        $existing = $this->service->findVocabulary($topicCloudId, $vocabCloudId);

        if (! $existing) {
            return response()->json(['message' => 'Vocabulary not found.'], 404);
        }

        $validated = $request->validate([
            'word' => ['sometimes', 'required', 'string', 'max:255'],
            'video_url' => ['sometimes', 'required', 'string', 'max:2048'],
            'definition' => ['sometimes', 'nullable', 'string'],
            'image_preview' => ['sometimes', 'nullable', 'string', 'max:2048'],
        ]);

        $vocabulary = $this->service->upsertVocabulary($topicCloudId, $vocabCloudId, [
            ...$existing,
            ...$validated,
        ]);

        return response()->json(['data' => $vocabulary]);
    }

    public function destroy(string $topicCloudId, string $vocabCloudId): JsonResponse
    {
        if (! $this->service->deleteVocabulary($topicCloudId, $vocabCloudId)) {
            return response()->json(['message' => 'Vocabulary not found.'], 404);
        }

        return response()->json(status: 204);
    }
}
