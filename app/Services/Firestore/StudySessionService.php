<?php

namespace App\Services\Firestore;

use Google\Cloud\Firestore\FirestoreClient;

class StudySessionService
{
    public function __construct(private readonly FirestoreClient $firestore)
    {
    }

    public function upload(array $payload): array
    {
        $uid = (string) $payload['uid'];
        $sessionId = (string) $payload['session_id'];

        $userRef = $this->firestore->collection('users')->document($uid);
        $sessionRef = $userRef->collection('study_sessions')->document($sessionId);

        $existingSession = $sessionRef->snapshot();
        if ($existingSession->exists()) {
            return [
                'ok' => true,
                'already_processed' => true,
                'message' => 'Session already uploaded.',
                'data' => [
                    'uid' => $uid,
                    'session_id' => $sessionId,
                    ...$existingSession->data(),
                ],
            ];
        }

        $summary = $payload['summary'] ?? [];
        $lessonProgressItems = $payload['lesson_progress'] ?? [];
        $learnedWords = $payload['learned_words'] ?? [];

        $newLearnedWords = $this->upsertLearnedWords($userRef, $uid, $learnedWords);
        $this->upsertLessonProgress($userRef, $lessonProgressItems);

        $sessionData = [
            'uid' => $uid,
            'session_id' => $sessionId,
            'started_at' => $payload['started_at'] ?? null,
            'completed_at' => $payload['completed_at'] ?? now()->toIso8601String(),
            'summary' => [
                'total_questions' => (int) ($summary['total_questions'] ?? 0),
                'correct_answers' => (int) ($summary['correct_answers'] ?? 0),
                'total_points' => (int) ($summary['total_points'] ?? 0),
            ],
            'lesson_progress_count' => count($lessonProgressItems),
            'learned_words_count' => count($learnedWords),
            'created_at' => now()->toIso8601String(),
        ];

        $sessionRef->set($sessionData);

        $this->upsertUserSummary($userRef, $uid, (int) ($summary['total_points'] ?? 0), $newLearnedWords);

        return [
            'ok' => true,
            'already_processed' => false,
            'message' => 'Session uploaded successfully.',
            'data' => [
                'uid' => $uid,
                'session_id' => $sessionId,
                'new_learned_words' => $newLearnedWords,
                'lesson_progress_upserted' => count($lessonProgressItems),
                'summary' => $sessionData['summary'],
            ],
        ];
    }

    private function upsertLearnedWords($userRef, string $uid, array $learnedWords): int
    {
        $newCount = 0;

        foreach ($learnedWords as $item) {
            if (! is_array($item) || empty($item['vocab_cloud_id'])) {
                continue;
            }

            $vocabCloudId = (string) $item['vocab_cloud_id'];
            $docId = sprintf('%s_%s', $uid, $vocabCloudId);
            $learnedWordRef = $userRef->collection('learned_words')->document($docId);
            $existing = $learnedWordRef->snapshot();

            if (! $existing->exists()) {
                $newCount++;
            }

            $learnedWordRef->set([
                'uid' => $uid,
                'vocab_cloud_id' => $vocabCloudId,
                'composite_id' => $docId,
                'learned_at' => $item['learned_at'] ?? now()->toIso8601String(),
                'updated_at' => now()->toIso8601String(),
            ], ['merge' => true]);
        }

        return $newCount;
    }

    private function upsertLessonProgress($userRef, array $lessonProgressItems): void
    {
        foreach ($lessonProgressItems as $item) {
            if (! is_array($item) || empty($item['lesson_cloud_id'])) {
                continue;
            }

            $lessonCloudId = (string) $item['lesson_cloud_id'];
            $progressRef = $userRef->collection('lesson_progress')->document($lessonCloudId);
            $existing = $progressRef->snapshot();
            $existingData = $existing->exists() ? $existing->data() : [];

            $incomingScore = (int) ($item['score'] ?? 0);
            $existingScore = (int) ($existingData['score'] ?? 0);

            $incomingCompletedAt = (string) ($item['completed_at'] ?? now()->toIso8601String());
            $existingCompletedAt = (string) ($existingData['completed_at'] ?? '');

            $finalCompletedAt = $incomingCompletedAt;
            if ($existingCompletedAt !== '' && strtotime($existingCompletedAt) !== false && strtotime($incomingCompletedAt) !== false) {
                $finalCompletedAt = strtotime($incomingCompletedAt) >= strtotime($existingCompletedAt)
                    ? $incomingCompletedAt
                    : $existingCompletedAt;
            }

            $progressRef->set([
                'lesson_cloud_id' => $lessonCloudId,
                'topic_cloud_id' => (string) ($item['topic_cloud_id'] ?? ''),
                'is_completed' => (bool) ($item['is_completed'] ?? false) || (bool) ($existingData['is_completed'] ?? false),
                'score' => max($incomingScore, $existingScore),
                'completed_at' => $finalCompletedAt,
                'updated_at' => now()->toIso8601String(),
            ], ['merge' => true]);
        }
    }

    private function upsertUserSummary($userRef, string $uid, int $earnedPoints, int $newLearnedWords): void
    {
        $userSnapshot = $userRef->snapshot();
        $userData = $userSnapshot->exists() ? $userSnapshot->data() : [];

        $currentTotalPoints = (int) ($userData['totalPoints'] ?? 0);
        $currentWordsLearned = (int) ($userData['wordsLearned'] ?? 0);

        $userRef->set([
            'uid' => $uid,
            'totalPoints' => $currentTotalPoints + max(0, $earnedPoints),
            'wordsLearned' => $currentWordsLearned + max(0, $newLearnedWords),
            'lastStudyDate' => now()->toIso8601String(),
            'updatedAt' => now()->toIso8601String(),
        ], ['merge' => true]);
    }
}
