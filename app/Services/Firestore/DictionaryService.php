<?php

namespace App\Services\Firestore;

use Google\Cloud\Firestore\FirestoreClient;

class DictionaryService
{
    public function __construct(private readonly FirestoreClient $firestore)
    {
    }

    public function listTopics(): array
    {
        $documents = $this->firestore->collection('dictionary_topics')->documents();
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

    public function findTopic(string $topicCloudId): ?array
    {
        $document = $this->firestore->collection('dictionary_topics')->document($topicCloudId)->snapshot();

        if (! $document->exists()) {
            return null;
        }

        return [
            'cloud_id' => $document->id(),
            ...$document->data(),
        ];
    }

    public function upsertTopic(string $topicCloudId, array $payload): array
    {
        $normalized = [
            'cloud_id' => $topicCloudId,
            'name' => $payload['name'],
            'icon_url' => $payload['icon_url'] ?? null,
            'order_index' => (int) ($payload['order_index'] ?? 0),
            'updated_at' => now()->toIso8601String(),
        ];

        $this->firestore
            ->collection('dictionary_topics')
            ->document($topicCloudId)
            ->set($normalized, ['merge' => true]);

        return $this->findTopic($topicCloudId) ?? $normalized;
    }

    public function deleteTopic(string $topicCloudId): bool
    {
        $topicRef = $this->firestore->collection('dictionary_topics')->document($topicCloudId);
        $snapshot = $topicRef->snapshot();

        if (! $snapshot->exists()) {
            return false;
        }

        foreach ($topicRef->collection('vocabularies')->documents() as $vocabularyDocument) {
            if (! $vocabularyDocument->exists()) {
                continue;
            }

            $topicRef->collection('vocabularies')->document($vocabularyDocument->id())->delete();
        }

        $topicRef->delete();

        return true;
    }

    public function listVocabularies(string $topicCloudId): array
    {
        $topicRef = $this->firestore->collection('dictionary_topics')->document($topicCloudId);
        $topicSnapshot = $topicRef->snapshot();

        if (! $topicSnapshot->exists()) {
            return [];
        }

        $vocabularies = [];

        foreach ($topicRef->collection('vocabularies')->documents() as $document) {
            if (! $document->exists()) {
                continue;
            }

            $vocabularies[] = [
                'cloud_id' => $document->id(),
                ...$document->data(),
            ];
        }

        usort($vocabularies, fn (array $a, array $b) => strcmp((string) ($a['word'] ?? ''), (string) ($b['word'] ?? '')));

        return $vocabularies;
    }

    public function findVocabulary(string $topicCloudId, string $vocabCloudId): ?array
    {
        $document = $this->firestore
            ->collection('dictionary_topics')
            ->document($topicCloudId)
            ->collection('vocabularies')
            ->document($vocabCloudId)
            ->snapshot();

        if (! $document->exists()) {
            return null;
        }

        return [
            'cloud_id' => $document->id(),
            ...$document->data(),
        ];
    }

    public function upsertVocabulary(string $topicCloudId, string $vocabCloudId, array $payload): ?array
    {
        $topicRef = $this->firestore->collection('dictionary_topics')->document($topicCloudId);

        if (! $topicRef->snapshot()->exists()) {
            return null;
        }

        $normalized = [
            'cloud_id' => $vocabCloudId,
            'topic_cloud_id' => $topicCloudId,
            'word' => $payload['word'],
            'video_url' => $payload['video_url'],
            'definition' => $payload['definition'] ?? null,
            'image_preview' => $payload['image_preview'] ?? null,
            'updated_at' => now()->toIso8601String(),
        ];

        $topicRef
            ->collection('vocabularies')
            ->document($vocabCloudId)
            ->set($normalized, ['merge' => true]);

        return $this->findVocabulary($topicCloudId, $vocabCloudId);
    }

    public function deleteVocabulary(string $topicCloudId, string $vocabCloudId): bool
    {
        $topicRef = $this->firestore->collection('dictionary_topics')->document($topicCloudId);

        if (! $topicRef->snapshot()->exists()) {
            return false;
        }

        $vocabularyRef = $topicRef->collection('vocabularies')->document($vocabCloudId);
        $vocabularySnapshot = $vocabularyRef->snapshot();

        if (! $vocabularySnapshot->exists()) {
            return false;
        }

        $vocabularyRef->delete();

        return true;
    }
}
