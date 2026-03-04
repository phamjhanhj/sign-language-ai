<?php

namespace App\Services\Firestore;

use Google\Cloud\Firestore\FirestoreClient;

class LearningTopicService
{
    public function __construct(private readonly FirestoreClient $firestore)
    {
    }

    public function list(): array
    {
        $documents = $this->firestore->collection('learning_topics')->documents();
        $topics = [];

        foreach ($documents as $document) {
            if (! $document->exists()) {
                continue;
            }

            $topics[] = [
                'cloud_id' => $document->id(),
                ...$document->data(),
            ];
        }

        usort($topics, fn (array $a, array $b) => ($a['order_index'] ?? 0) <=> ($b['order_index'] ?? 0));

        return $topics;
    }

    public function find(string $topicCloudId): ?array
    {
        $document = $this->firestore->collection('learning_topics')->document($topicCloudId)->snapshot();

        if (! $document->exists()) {
            return null;
        }

        return [
            'cloud_id' => $document->id(),
            ...$document->data(),
        ];
    }

    public function upsert(string $topicCloudId, array $payload): array
    {
        $normalized = [
            'cloud_id' => $topicCloudId,
            'title' => $payload['title'],
            'description' => $payload['description'] ?? null,
            'thumbnail_url' => $payload['thumbnail_url'] ?? null,
            'order_index' => (int) ($payload['order_index'] ?? 0),
            'is_active' => (bool) ($payload['is_active'] ?? true),
            'updated_at' => now()->toIso8601String(),
        ];

        $this->firestore
            ->collection('learning_topics')
            ->document($topicCloudId)
            ->set($normalized, ['merge' => true]);

        return $this->find($topicCloudId) ?? $normalized;
    }

    public function delete(string $topicCloudId): bool
    {
        $document = $this->firestore->collection('learning_topics')->document($topicCloudId)->snapshot();

        if (! $document->exists()) {
            return false;
        }

        $this->firestore->collection('learning_topics')->document($topicCloudId)->delete();

        return true;
    }

    public function listLessons(string $topicCloudId): array
    {
        $topicRef = $this->firestore->collection('learning_topics')->document($topicCloudId);
        $lessons = [];

        foreach ($topicRef->collection('lessons')->documents() as $doc) {
            if (! $doc->exists()) {
                continue;
            }
            $data = $doc->data();
            $data['cloud_id'] = $doc->id();
            $lessons[] = $data;
        }

        usort($lessons, fn ($a, $b) => ($a['order'] ?? 0) <=> ($b['order'] ?? 0));

        return $lessons;
    }

    public function generateLessons(string $topicCloudId, array $vocabularies, array $sentences): array
    {
        $topic = $this->find($topicCloudId);

        if (! $topic) {
            return [
                'ok' => false,
                'message' => 'Learning topic not found.',
            ];
        }

        $normalizedVocabularies = collect($vocabularies)
            ->filter(fn ($item) => is_array($item) && ! empty($item['cloud_id']) && ! empty($item['word']))
            ->map(function (array $item): array {
                return [
                    'cloud_id' => (string) $item['cloud_id'],
                    'word' => (string) $item['word'],
                    'video_url' => (string) ($item['video_url'] ?? ''),
                    'explanation' => (string) ($item['explanation'] ?? ($item['definition'] ?? '')),
                ];
            })
            ->values()
            ->all();

        $normalizedSentences = collect($sentences)
            ->filter(fn ($item) => is_array($item) && ! empty($item['text']))
            ->map(function (array $item): array {
                $tokens = collect($item['tokens'] ?? preg_split('/\s+/u', trim((string) $item['text'])))
                    ->filter(fn ($token) => is_string($token) && trim($token) !== '')
                    ->map(fn ($token) => trim((string) $token))
                    ->values()
                    ->all();

                return [
                    'text' => trim((string) $item['text']),
                    'video_url' => (string) ($item['video_url'] ?? ''),
                    'tokens' => $tokens,
                    'related_vocab_ids' => array_values(array_map('strval', $item['related_vocab_ids'] ?? [])),
                ];
            })
            ->filter(fn (array $item) => $item['text'] !== '' && count($item['tokens']) > 1)
            ->values()
            ->all();

        if (count($normalizedVocabularies) < 4 || count($normalizedSentences) < 4) {
            return [
                'ok' => false,
                'message' => 'Need at least 4 vocabularies and 4 sentences to generate 8 lessons.',
            ];
        }

        $lessons = $this->buildEightLessons($topicCloudId, $normalizedVocabularies, $normalizedSentences);

        $topicRef = $this->firestore->collection('learning_topics')->document($topicCloudId);
        $this->deleteAllLessons($topicRef);

        foreach ($lessons as $lesson) {
            $lessonRef = $topicRef->collection('lessons')->document($lesson['cloud_id']);
            $questions = $lesson['questions'];
            unset($lesson['questions']);

            $lessonRef->set([
                ...$lesson,
                'topic_cloud_id' => $topicCloudId,
                'updated_at' => now()->toIso8601String(),
            ]);

            foreach ($questions as $question) {
                $lessonRef->collection('questions')->document($question['cloud_id'])->set([
                    ...$question,
                    'updated_at' => now()->toIso8601String(),
                ]);
            }
        }

        return [
            'ok' => true,
            'message' => 'Generated 8 lessons successfully.',
            'data' => [
                'topic_cloud_id' => $topicCloudId,
                'lesson_count' => count($lessons),
                'question_count' => array_sum(array_map(fn ($lesson) => count($lesson['questions']), $lessons)),
                'lessons' => $lessons,
            ],
        ];
    }

    private function buildEightLessons(string $topicCloudId, array $vocabularies, array $sentences): array
    {
        $now = now()->toIso8601String();

        $lessonTemplates = [
            ['suffix' => '01_foundation_vocab', 'title' => 'Lesson 1: Foundation Vocabulary', 'mode' => 'learn_vocab'],
            ['suffix' => '02_sentence_learn', 'title' => 'Lesson 2: Learn Sentences', 'mode' => 'learn_sentence'],
            ['suffix' => '03_choice_basic', 'title' => 'Lesson 3: Choice Basic', 'mode' => 'choice_basic'],
            ['suffix' => '04_arrange_basic', 'title' => 'Lesson 4: Arrange Basic', 'mode' => 'arrange_basic'],
            ['suffix' => '05_choice_intermediate', 'title' => 'Lesson 5: Choice Intermediate', 'mode' => 'choice_mid'],
            ['suffix' => '06_arrange_intermediate', 'title' => 'Lesson 6: Arrange Intermediate', 'mode' => 'arrange_mid'],
            ['suffix' => '07_mixed_review', 'title' => 'Lesson 7: Mixed Review', 'mode' => 'mixed'],
            ['suffix' => '08_final_test', 'title' => 'Lesson 8: Final Test', 'mode' => 'final'],
        ];

        $lessons = [];

        foreach ($lessonTemplates as $index => $template) {
            $orderIndex = $index + 1;
            $lessonCloudId = sprintf('ls_%s_%s', $topicCloudId, $template['suffix']);

            $questions = $this->buildLessonQuestions($lessonCloudId, $template['mode'], $vocabularies, $sentences);

            $lessons[] = [
                'cloud_id' => $lessonCloudId,
                'title' => $template['title'],
                'description' => 'Auto-generated lesson from template.',
                'order_index' => $orderIndex,
                'is_generated' => true,
                'generated_mode' => $template['mode'],
                'generated_at' => $now,
                'questions' => $questions,
            ];
        }

        return $lessons;
    }

    private function buildLessonQuestions(string $lessonCloudId, string $mode, array $vocabularies, array $sentences): array
    {
        return match ($mode) {
            'learn_vocab' => $this->buildLearnVocabularyQuestions($lessonCloudId, array_slice($vocabularies, 0, 6)),
            'learn_sentence' => $this->buildLearnSentenceQuestions($lessonCloudId, array_slice($sentences, 0, 5)),
            'choice_basic' => $this->buildChoiceQuestions($lessonCloudId, array_slice($vocabularies, 0, 6), 2),
            'arrange_basic' => $this->buildArrangeQuestions($lessonCloudId, array_slice($sentences, 0, 4), false),
            'choice_mid' => $this->buildChoiceQuestions($lessonCloudId, array_slice($vocabularies, 2, 8), 3),
            'arrange_mid' => $this->buildArrangeQuestions($lessonCloudId, array_slice($sentences, 1, 6), true),
            'mixed' => array_merge(
                $this->buildChoiceQuestions($lessonCloudId, array_slice($vocabularies, 0, 3), 3, 1),
                $this->buildArrangeQuestions($lessonCloudId, array_slice($sentences, 0, 2), false, 4)
            ),
            'final' => array_merge(
                $this->buildChoiceQuestions($lessonCloudId, array_slice($vocabularies, 0, 4), 4, 1),
                $this->buildArrangeQuestions($lessonCloudId, array_slice($sentences, 0, 3), true, 5)
            ),
            default => [],
        };
    }

    private function buildLearnVocabularyQuestions(string $lessonCloudId, array $vocabularies): array
    {
        $questions = [];

        foreach ($vocabularies as $index => $vocabulary) {
            $questions[] = [
                'cloud_id' => sprintf('qs_%s_%02d', $lessonCloudId, $index + 1),
                'type' => 'learn',
                'order_index' => $index + 1,
                'related_vocab_ids' => [$vocabulary['cloud_id']],
                'data' => [
                    'video_url' => $vocabulary['video_url'],
                    'explanation' => $vocabulary['explanation'] !== ''
                        ? $vocabulary['explanation']
                        : sprintf("Ký hiệu cho từ '%s'.", $vocabulary['word']),
                ],
            ];
        }

        return $questions;
    }

    private function buildLearnSentenceQuestions(string $lessonCloudId, array $sentences): array
    {
        $questions = [];

        foreach ($sentences as $index => $sentence) {
            $questions[] = [
                'cloud_id' => sprintf('qs_%s_%02d', $lessonCloudId, $index + 1),
                'type' => 'learn',
                'order_index' => $index + 1,
                'related_vocab_ids' => $sentence['related_vocab_ids'],
                'data' => [
                    'video_url' => $sentence['video_url'],
                    'explanation' => $sentence['text'],
                ],
            ];
        }

        return $questions;
    }

    private function buildChoiceQuestions(string $lessonCloudId, array $vocabularies, int $optionCount, int $startOrderIndex = 1): array
    {
        $questions = [];
        $poolWords = array_values(array_map(fn ($item) => $item['word'], $vocabularies));

        foreach ($vocabularies as $index => $vocabulary) {
            $options = collect($poolWords)
                ->reject(fn ($word) => $word === $vocabulary['word'])
                ->shuffle()
                ->take(max(1, $optionCount - 1))
                ->push($vocabulary['word'])
                ->shuffle()
                ->values()
                ->all();

            $questions[] = [
                'cloud_id' => sprintf('qs_%s_%02d', $lessonCloudId, $startOrderIndex + $index),
                'type' => 'choice',
                'order_index' => $startOrderIndex + $index,
                'related_vocab_ids' => [$vocabulary['cloud_id']],
                'data' => [
                    'video_url' => $vocabulary['video_url'],
                    'correct_answer' => $vocabulary['word'],
                    'options' => $options,
                ],
            ];
        }

        return $questions;
    }

    private function buildArrangeQuestions(string $lessonCloudId, array $sentences, bool $addDistractor = false, int $startOrderIndex = 1): array
    {
        $questions = [];

        foreach ($sentences as $index => $sentence) {
            $words = $sentence['tokens'];
            $shuffledWords = collect($words)->shuffle();

            if ($addDistractor) {
                $shuffledWords->push('...');
            }

            $questions[] = [
                'cloud_id' => sprintf('qs_%s_%02d', $lessonCloudId, $startOrderIndex + $index),
                'type' => 'arrange',
                'order_index' => $startOrderIndex + $index,
                'related_vocab_ids' => $sentence['related_vocab_ids'],
                'data' => [
                    'video_url' => $sentence['video_url'],
                    'correct_sentence' => $sentence['text'],
                    'shuffled_words' => $shuffledWords->values()->all(),
                ],
            ];
        }

        return $questions;
    }

    private function deleteAllLessons($topicRef): void
    {
        foreach ($topicRef->collection('lessons')->documents() as $lessonDocument) {
            if (! $lessonDocument->exists()) {
                continue;
            }

            $lessonRef = $topicRef->collection('lessons')->document($lessonDocument->id());

            foreach ($lessonRef->collection('questions')->documents() as $questionDocument) {
                if (! $questionDocument->exists()) {
                    continue;
                }

                $lessonRef->collection('questions')->document($questionDocument->id())->delete();
            }

            $lessonRef->delete();
        }
    }
}
