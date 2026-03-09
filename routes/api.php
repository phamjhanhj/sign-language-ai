<?php

use App\Http\Controllers\Api\AdminContentController;
use App\Http\Controllers\Api\ContentSyncController;
use App\Http\Controllers\Api\DictionaryTopicController;
use App\Http\Controllers\Api\DictionaryVocabularyController;
use App\Http\Controllers\Api\LearningTopicController;
use App\Http\Controllers\Api\StudySessionController;
use Illuminate\Support\Facades\Route;

Route::get('content/version', [ContentSyncController::class, 'version']);
Route::get('content/check-version', [ContentSyncController::class, 'checkVersion']);
Route::get('content/bootstrap', [ContentSyncController::class, 'contentBootstrap']);
Route::get('dictionary/data', [ContentSyncController::class, 'dictionaryBootstrap']);
Route::get('dictionary/bootstrap', [ContentSyncController::class, 'dictionaryBootstrap']);

// ── Mobile endpoints ─────────────────────────────────────────
Route::get('topics', [ContentSyncController::class, 'contentBootstrap']);
Route::get('dictionary', [ContentSyncController::class, 'dictionaryBootstrap']);
Route::post('study-sessions/upload', [StudySessionController::class, 'upload']);

Route::prefix('admin')->group(function () {
    Route::post('content/publish', [AdminContentController::class, 'publish']);

    Route::get('learning-topics', [LearningTopicController::class, 'index']);
    Route::post('learning-topics', [LearningTopicController::class, 'store']);
    Route::get('learning-topics/{topicCloudId}', [LearningTopicController::class, 'show']);
    Route::put('learning-topics/{topicCloudId}', [LearningTopicController::class, 'update']);
    Route::delete('learning-topics/{topicCloudId}', [LearningTopicController::class, 'destroy']);
    Route::post('learning-topics/{topicCloudId}/generate-lessons', [LearningTopicController::class, 'generateLessons']);

    Route::get('dictionary-topics', [DictionaryTopicController::class, 'index']);
    Route::post('dictionary-topics', [DictionaryTopicController::class, 'store']);
    Route::get('dictionary-topics/{topicCloudId}', [DictionaryTopicController::class, 'show']);
    Route::put('dictionary-topics/{topicCloudId}', [DictionaryTopicController::class, 'update']);
    Route::delete('dictionary-topics/{topicCloudId}', [DictionaryTopicController::class, 'destroy']);

    Route::get('dictionary-topics/{topicCloudId}/vocabularies', [DictionaryVocabularyController::class, 'index']);
    Route::post('dictionary-topics/{topicCloudId}/vocabularies', [DictionaryVocabularyController::class, 'store']);
    Route::get('dictionary-topics/{topicCloudId}/vocabularies/{vocabCloudId}', [DictionaryVocabularyController::class, 'show']);
    Route::put('dictionary-topics/{topicCloudId}/vocabularies/{vocabCloudId}', [DictionaryVocabularyController::class, 'update']);
    Route::delete('dictionary-topics/{topicCloudId}/vocabularies/{vocabCloudId}', [DictionaryVocabularyController::class, 'destroy']);
});
