<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Firestore\StudySessionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StudySessionController extends Controller
{
    public function __construct(private readonly StudySessionService $service)
    {
    }

    public function upload(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'uid' => ['required', 'string', 'max:191'],
            'session_id' => ['required', 'string', 'max:191'],
            'started_at' => ['nullable', 'date'],
            'completed_at' => ['nullable', 'date'],

            'summary' => ['required', 'array'],
            'summary.total_questions' => ['nullable', 'integer', 'min:0'],
            'summary.correct_answers' => ['nullable', 'integer', 'min:0'],
            'summary.total_points' => ['nullable', 'integer', 'min:0'],

            'lesson_progress' => ['nullable', 'array'],
            'lesson_progress.*.lesson_cloud_id' => ['required_with:lesson_progress', 'string', 'max:120'],
            'lesson_progress.*.topic_cloud_id' => ['nullable', 'string', 'max:120'],
            'lesson_progress.*.is_completed' => ['nullable', 'boolean'],
            'lesson_progress.*.score' => ['nullable', 'integer', 'min:0'],
            'lesson_progress.*.completed_at' => ['nullable', 'date'],

            'learned_words' => ['nullable', 'array'],
            'learned_words.*.vocab_cloud_id' => ['required_with:learned_words', 'string', 'max:120'],
            'learned_words.*.learned_at' => ['nullable', 'date'],
        ]);

        $result = $this->service->upload($validated);
        $status = ($result['already_processed'] ?? false) ? 200 : 201;

        return response()->json($result, $status);
    }
}
