<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Firestore\DictionaryService;
use Illuminate\Http\Request;

class VocabularyWebController extends Controller
{
    public function __construct(private readonly DictionaryService $service) {}

    public function index(string $topicCloudId)
    {
        $topic = $this->service->findTopic($topicCloudId);
        $topicTitle = $topic['name'] ?? $topic['title'] ?? $topicCloudId;

        return view('admin.vocabularies.index', [
            'topicCloudId'  => $topicCloudId,
            'topicTitle'    => $topicTitle,
            'vocabularies'  => $this->service->listVocabularies($topicCloudId),
        ]);
    }

    public function create(string $topicCloudId)
    {
        $topic = $this->service->findTopic($topicCloudId);

        return view('admin.vocabularies.form', [
            'topicCloudId' => $topicCloudId,
            'topicTitle'   => $topic['name'] ?? $topic['title'] ?? $topicCloudId,
        ]);
    }

    public function store(Request $request, string $topicCloudId)
    {
        $request->validate([
            'word' => 'required|string|max:255',
            'video_url' => 'nullable|url|max:500',
            'thumbnail_url' => 'nullable|url|max:500',
            'order' => 'nullable|integer|min:0',
        ]);

        $vocabId = uniqid('v_');
        $this->service->upsertVocabulary($topicCloudId, $vocabId, [
            'word'          => $request->input('word'),
            'video_url'     => $request->input('video_url'),
            'definition'    => $request->input('description'),
            'image_preview' => $request->input('thumbnail_url'),
        ]);

        return redirect()->route('admin.vocabularies.index', $topicCloudId)
                         ->with('success', 'Vocabulary added.');
    }

    public function edit(string $topicCloudId, string $vocabCloudId)
    {
        $topic = $this->service->findTopic($topicCloudId);
        $vocab = $this->service->findVocabulary($topicCloudId, $vocabCloudId);

        if (! $vocab) {
            return redirect()->route('admin.vocabularies.index', $topicCloudId)->with('error', 'Vocabulary not found.');
        }

        return view('admin.vocabularies.form', [
            'topicCloudId' => $topicCloudId,
            'topicTitle'   => $topic['name'] ?? $topic['title'] ?? $topicCloudId,
            'vocab'        => $vocab,
        ]);
    }

    public function update(Request $request, string $topicCloudId, string $vocabCloudId)
    {
        $request->validate([
            'word' => 'required|string|max:255',
            'video_url' => 'nullable|url|max:500',
            'thumbnail_url' => 'nullable|url|max:500',
            'order' => 'nullable|integer|min:0',
        ]);

        $this->service->upsertVocabulary($topicCloudId, $vocabCloudId, [
            'word'          => $request->input('word'),
            'video_url'     => $request->input('video_url'),
            'definition'    => $request->input('description'),
            'image_preview' => $request->input('thumbnail_url'),
        ]);

        return redirect()->route('admin.vocabularies.index', $topicCloudId)
                         ->with('success', 'Vocabulary updated.');
    }

    public function destroy(string $topicCloudId, string $vocabCloudId)
    {
        $this->service->deleteVocabulary($topicCloudId, $vocabCloudId);

        return redirect()->route('admin.vocabularies.index', $topicCloudId)
                         ->with('success', 'Vocabulary deleted.');
    }
}
