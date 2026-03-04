<?php

namespace App\Console\Commands;

use App\Services\Firestore\DictionaryService;
use Illuminate\Console\Command;

class ImportDictionaryData extends Command
{
    protected $signature = 'dictionary:import {file? : Path to JSON file}';
    protected $description = 'Import dictionary data (topics + vocabularies) from JSON file into Firestore';

    public function __construct(private readonly DictionaryService $dictionaryService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $file = $this->argument('file') ?? storage_path('app/data/dictionary_data.json');

        if (! file_exists($file)) {
            $this->error("File not found: {$file}");
            return self::FAILURE;
        }

        $data = json_decode(file_get_contents($file), true);

        if (! $data || ! isset($data['topics'])) {
            $this->error('Invalid JSON format. Expected "topics" array.');
            return self::FAILURE;
        }

        $topics = $data['topics'];
        $this->info("Found " . count($topics) . " topics to import.");

        $totalVocabs = 0;

        foreach ($topics as $index => $topic) {
            $topicCloudId = $topic['cloud_id'];
            $topicName = $topic['name'];
            $vocabularies = $topic['vocabularies'] ?? [];

            $this->info("");
            $this->info("[$topicCloudId] Creating topic: {$topicName}");

            $this->dictionaryService->upsertTopic($topicCloudId, [
                'name' => $topicName,
                'icon_url' => $topic['icon_url'] ?? null,
                'order_index' => $index + 1,
            ]);

            $this->output->write("  Vocabularies: ");

            foreach ($vocabularies as $vocab) {
                $vocabCloudId = $vocab['cloud_id'];

                $this->dictionaryService->upsertVocabulary($topicCloudId, $vocabCloudId, [
                    'word' => $vocab['word'],
                    'video_url' => $vocab['video_url'],
                    'definition' => $vocab['definition'] ?? null,
                    'image_preview' => $vocab['image_preview'] ?? null,
                ]);

                $this->output->write(".");
                $totalVocabs++;
            }

            $this->info(" " . count($vocabularies) . " done");
        }

        $this->info("");
        $this->info("=== Import complete ===");
        $this->info("Topics: " . count($topics));
        $this->info("Vocabularies: {$totalVocabs}");

        return self::SUCCESS;
    }
}
