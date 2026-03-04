<?php

namespace App\Services\Firestore;

use Google\Cloud\Firestore\FirestoreClient;

class ContentPublishService
{
    public function __construct(private readonly FirestoreClient $firestore)
    {
    }

    public function publish(?string $notes = null, ?string $publishedBy = null): array
    {
        $currentMetaRef = $this->firestore->collection('content_meta')->document('current');
        $currentSnapshot = $currentMetaRef->snapshot();
        $currentData = $currentSnapshot->exists() ? $currentSnapshot->data() : [];
        $nextVersion = (int) ($currentData['version'] ?? 0) + 1;

        $learningPayload = $this->buildLearningBootstrapPayload();
        $dictionaryPayload = $this->buildDictionaryBootstrapPayload($nextVersion);

        $checksumSource = [
            'learning' => $learningPayload,
            'dictionary' => $dictionaryPayload,
        ];

        $checksum = $this->checksum($checksumSource);
        $publishedAt = now()->toIso8601String();

        $versionRef = $this->firestore->collection('content_versions')->document((string) $nextVersion);

        $versionRef->set([
            'version' => $nextVersion,
            'checksum' => $checksum,
            'published_at' => $publishedAt,
            'published_by' => $publishedBy,
            'notes' => $notes,
            'learning_topic_count' => count($learningPayload['topics'] ?? []),
            'dictionary_topic_count' => count($dictionaryPayload['topics'] ?? []),
        ]);

        $versionRef->collection('payload')->document('learning')->set($learningPayload);
        $versionRef->collection('payload')->document('dictionary')->set($dictionaryPayload);

        $currentMetaRef->set([
            'version' => $nextVersion,
            'checksum' => $checksum,
            'published_at' => $publishedAt,
            'learning_version' => $nextVersion,
            'dictionary_version' => $nextVersion,
            'published_by' => $publishedBy,
            'notes' => $notes,
        ], ['merge' => true]);

        return [
            'version' => $nextVersion,
            'checksum' => $checksum,
            'published_at' => $publishedAt,
            'learning_topic_count' => count($learningPayload['topics'] ?? []),
            'dictionary_topic_count' => count($dictionaryPayload['topics'] ?? []),
        ];
    }

    public function getCurrentVersionMeta(): array
    {
        $snapshot = $this->firestore->collection('content_meta')->document('current')->snapshot();

        if (! $snapshot->exists()) {
            return [
                'version' => 0,
                'checksum' => null,
                'published_at' => null,
                'published' => false,
            ];
        }

        $data = $snapshot->data();

        return [
            'version' => (int) ($data['version'] ?? 0),
            'checksum' => $data['checksum'] ?? null,
            'published_at' => $data['published_at'] ?? null,
            'published' => (int) ($data['version'] ?? 0) > 0,
        ];
    }

    public function getLearningBootstrapPayload(): ?array
    {
        $version = $this->getCurrentVersionMeta()['version'] ?? 0;

        if ($version < 1) {
            return null;
        }

        $snapshot = $this->firestore
            ->collection('content_versions')
            ->document((string) $version)
            ->collection('payload')
            ->document('learning')
            ->snapshot();

        return $snapshot->exists() ? $snapshot->data() : null;
    }

    public function getDictionaryBootstrapPayload(): ?array
    {
        $version = $this->getCurrentVersionMeta()['version'] ?? 0;

        if ($version < 1) {
            return null;
        }

        $snapshot = $this->firestore
            ->collection('content_versions')
            ->document((string) $version)
            ->collection('payload')
            ->document('dictionary')
            ->snapshot();

        return $snapshot->exists() ? $snapshot->data() : null;
    }

    private function buildLearningBootstrapPayload(): array
    {
        $topics = [];

        foreach ($this->firestore->collection('learning_topics')->documents() as $topicDocument) {
            if (! $topicDocument->exists()) {
                continue;
            }

            $topicData = $topicDocument->data();
            $topicCloudId = $topicDocument->id();
            $topicRef = $this->firestore->collection('learning_topics')->document($topicCloudId);

            $lessons = [];
            foreach ($topicRef->collection('lessons')->documents() as $lessonDocument) {
                if (! $lessonDocument->exists()) {
                    continue;
                }

                $lessonData = $lessonDocument->data();
                $lessonCloudId = $lessonDocument->id();
                $lessonRef = $topicRef->collection('lessons')->document($lessonCloudId);

                $questions = [];
                foreach ($lessonRef->collection('questions')->documents() as $questionDocument) {
                    if (! $questionDocument->exists()) {
                        continue;
                    }

                    $questionData = $questionDocument->data();
                    $normalizedQuestion = $this->normalizeQuestionForFlutter($questionDocument->id(), $questionData);

                    $questions[] = $normalizedQuestion;
                }

                usort($questions, fn (array $a, array $b) => ($a['order_index'] ?? 0) <=> ($b['order_index'] ?? 0));

                $lessons[] = [
                    'cloud_id' => $lessonCloudId,
                    'title' => (string) ($lessonData['title'] ?? ''),
                    'description' => $lessonData['description'] ?? null,
                    'order_index' => (int) ($lessonData['order_index'] ?? 0),
                    'questions' => $questions,
                ];
            }

            usort($lessons, fn (array $a, array $b) => ($a['order_index'] ?? 0) <=> ($b['order_index'] ?? 0));

            $topics[] = [
                'cloud_id' => $topicCloudId,
                'title' => (string) ($topicData['title'] ?? ''),
                'description' => $topicData['description'] ?? null,
                'thumbnail_url' => $topicData['thumbnail_url'] ?? null,
                'order_index' => (int) ($topicData['order_index'] ?? 0),
                'lessons' => $lessons,
            ];
        }

        usort($topics, fn (array $a, array $b) => ($a['order_index'] ?? 0) <=> ($b['order_index'] ?? 0));

        return [
            'system_info' => [
                'data_version' => 1,
                'last_updated' => now()->toIso8601String(),
                'description' => 'Published snapshot',
            ],
            'topics' => $topics,
        ];
    }

    private function buildDictionaryBootstrapPayload(?int $version = null): array
    {
        $topics = [];

        foreach ($this->firestore->collection('dictionary_topics')->documents() as $topicDocument) {
            if (! $topicDocument->exists()) {
                continue;
            }

            $topicCloudId = $topicDocument->id();
            $topicData = $topicDocument->data();
            $topicRef = $this->firestore->collection('dictionary_topics')->document($topicCloudId);

            $vocabularies = [];
            foreach ($topicRef->collection('vocabularies')->documents() as $vocabularyDocument) {
                if (! $vocabularyDocument->exists()) {
                    continue;
                }

                $vocabularyData = $vocabularyDocument->data();

                $vocabularies[] = [
                    'cloud_id' => $vocabularyDocument->id(),
                    'word' => (string) ($vocabularyData['word'] ?? ''),
                    'video_url' => (string) ($vocabularyData['video_url'] ?? ''),
                    'definition' => $vocabularyData['definition'] ?? null,
                ];
            }

            usort($vocabularies, fn (array $a, array $b) => strcmp((string) ($a['word'] ?? ''), (string) ($b['word'] ?? '')));

            $topics[] = [
                'cloud_id' => $topicCloudId,
                'name' => (string) ($topicData['name'] ?? ''),
                'vocabularies' => $vocabularies,
            ];
        }

        usort($topics, fn (array $a, array $b) => strcmp((string) ($a['name'] ?? ''), (string) ($b['name'] ?? '')));

        return [
            'dictionary_info' => [
                'version' => $version ?? $this->getCurrentVersionMeta()['version'],
                'last_updated' => now()->format('Y-m-d'),
            ],
            'topics' => $topics,
        ];
    }

    private function normalizeQuestionForFlutter(string $questionCloudId, array $questionData): array
    {
        $type = (string) ($questionData['type'] ?? 'learn');
        $data = is_array($questionData['data'] ?? null) ? $questionData['data'] : [];

        $contentText = '';
        $options = [];

        if ($type === 'learn') {
            $contentText = (string) ($data['explanation'] ?? '');
        } elseif ($type === 'choice') {
            $contentText = (string) ($data['correct_answer'] ?? '');
            $options = array_values(array_map('strval', $data['options'] ?? []));
        } elseif ($type === 'arrange') {
            $contentText = (string) ($data['correct_sentence'] ?? '');
            $options = array_values(array_map('strval', $data['shuffled_words'] ?? []));
        }

        return [
            'cloud_id' => $questionCloudId,
            'type' => $type,
            'content_text' => $contentText,
            'video_url' => (string) ($data['video_url'] ?? ''),
            'options' => $options,
            'related_vocab_ids' => array_values(array_map('strval', $questionData['related_vocab_ids'] ?? [])),
            'order_index' => (int) ($questionData['order_index'] ?? 0),
        ];
    }

    private function checksum(array $value): string
    {
        $normalized = $this->canonicalize($value);
        $json = json_encode($normalized, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        return 'sha256:'.hash('sha256', (string) $json);
    }

    private function canonicalize(mixed $value): mixed
    {
        if (! is_array($value)) {
            return $value;
        }

        if (array_is_list($value)) {
            return array_map(fn ($item) => $this->canonicalize($item), $value);
        }

        ksort($value);

        foreach ($value as $key => $item) {
            $value[$key] = $this->canonicalize($item);
        }

        return $value;
    }
}
