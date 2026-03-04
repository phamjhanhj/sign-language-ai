<?php

use App\Http\Controllers\Admin\ContentWebController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DictionaryTopicWebController;
use App\Http\Controllers\Admin\LearningTopicWebController;
use App\Http\Controllers\Admin\VocabularyWebController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('admin.dashboard');
});

/*
|--------------------------------------------------------------------------
| Admin Web Routes
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->name('admin.')->group(function () {
    // Dashboard
    Route::get('/', DashboardController::class)->name('dashboard');

    // Learning Topics
    Route::get('learning-topics', [LearningTopicWebController::class, 'index'])->name('learning-topics.index');
    Route::get('learning-topics/create', [LearningTopicWebController::class, 'create'])->name('learning-topics.create');
    Route::post('learning-topics', [LearningTopicWebController::class, 'store'])->name('learning-topics.store');
    Route::get('learning-topics/{topicCloudId}', [LearningTopicWebController::class, 'show'])->name('learning-topics.show');
    Route::get('learning-topics/{topicCloudId}/edit', [LearningTopicWebController::class, 'edit'])->name('learning-topics.edit');
    Route::put('learning-topics/{topicCloudId}', [LearningTopicWebController::class, 'update'])->name('learning-topics.update');
    Route::delete('learning-topics/{topicCloudId}', [LearningTopicWebController::class, 'destroy'])->name('learning-topics.destroy');
    Route::post('learning-topics/{topicCloudId}/generate-lessons', [LearningTopicWebController::class, 'generateLessons'])->name('learning-topics.generate-lessons');

    // Dictionary Topics
    Route::get('dictionary-topics', [DictionaryTopicWebController::class, 'index'])->name('dictionary-topics.index');
    Route::get('dictionary-topics/create', [DictionaryTopicWebController::class, 'create'])->name('dictionary-topics.create');
    Route::post('dictionary-topics', [DictionaryTopicWebController::class, 'store'])->name('dictionary-topics.store');
    Route::get('dictionary-topics/{topicCloudId}/edit', [DictionaryTopicWebController::class, 'edit'])->name('dictionary-topics.edit');
    Route::put('dictionary-topics/{topicCloudId}', [DictionaryTopicWebController::class, 'update'])->name('dictionary-topics.update');
    Route::delete('dictionary-topics/{topicCloudId}', [DictionaryTopicWebController::class, 'destroy'])->name('dictionary-topics.destroy');

    // Vocabularies
    Route::get('dictionary-topics/{topicCloudId}/vocabularies', [VocabularyWebController::class, 'index'])->name('vocabularies.index');
    Route::get('dictionary-topics/{topicCloudId}/vocabularies/create', [VocabularyWebController::class, 'create'])->name('vocabularies.create');
    Route::post('dictionary-topics/{topicCloudId}/vocabularies', [VocabularyWebController::class, 'store'])->name('vocabularies.store');
    Route::get('dictionary-topics/{topicCloudId}/vocabularies/{vocabCloudId}/edit', [VocabularyWebController::class, 'edit'])->name('vocabularies.edit');
    Route::put('dictionary-topics/{topicCloudId}/vocabularies/{vocabCloudId}', [VocabularyWebController::class, 'update'])->name('vocabularies.update');
    Route::delete('dictionary-topics/{topicCloudId}/vocabularies/{vocabCloudId}', [VocabularyWebController::class, 'destroy'])->name('vocabularies.destroy');

    // Content Publish
    Route::get('content', [ContentWebController::class, 'index'])->name('content.index');
    Route::post('content/publish', [ContentWebController::class, 'publish'])->name('content.publish');
});
