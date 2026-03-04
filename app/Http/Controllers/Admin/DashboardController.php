<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Firestore\ContentPublishService;
use App\Services\Firestore\DictionaryService;
use App\Services\Firestore\LearningTopicService;

class DashboardController extends Controller
{
    public function __invoke(
        LearningTopicService $learningService,
        DictionaryService $dictionaryService,
        ContentPublishService $publishService,
    ) {
        $learningTopics = $learningService->list();
        $dictionaryTopics = $dictionaryService->listTopics();
        $meta = $publishService->getCurrentVersionMeta();

        $totalVocabularies = 0;
        foreach ($dictionaryTopics as $topic) {
            $totalVocabularies += count($dictionaryService->listVocabularies($topic['cloud_id']));
        }

        return view('admin.dashboard', [
            'stats' => [
                'learning_topics'    => count($learningTopics),
                'dictionary_topics'  => count($dictionaryTopics),
                'current_version'    => $meta['version'] ?? 0,
                'total_vocabularies' => $totalVocabularies,
            ],
        ]);
    }
}
