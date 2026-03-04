<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Firestore\DictionaryService;
use Illuminate\Http\Request;

class DictionaryTopicWebController extends Controller
{
    public function __construct(private readonly DictionaryService $service) {}

    public function index()
    {
        $topics = array_map(fn ($t) => $this->mapToView($t), $this->service->listTopics());

        return view('admin.dictionary-topics.index', [
            'topics' => $topics,
        ]);
    }

    public function create()
    {
        return view('admin.dictionary-topics.form');
    }

    /**
     * Map form fields to Firestore service fields.
     */
    private function mapToService(Request $request): array
    {
        return [
            'name'        => $request->input('title'),
            'icon_url'    => $request->input('thumbnail_url'),
            'order_index' => (int) $request->input('order', 0),
        ];
    }

    /**
     * Map Firestore document fields to view-friendly keys.
     */
    private function mapToView(?array $topic): ?array
    {
        if (! $topic) return null;
        return [
            'cloud_id'      => $topic['cloud_id'] ?? null,
            'title'         => $topic['name'] ?? $topic['title'] ?? '',
            'description'   => $topic['description'] ?? '',
            'thumbnail_url' => $topic['icon_url'] ?? $topic['thumbnail_url'] ?? '',
            'order'         => $topic['order_index'] ?? $topic['order'] ?? 0,
        ];
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'thumbnail_url' => 'nullable|url|max:500',
            'order' => 'nullable|integer|min:0',
        ]);

        $cloudId = uniqid('dt_');
        $this->service->upsertTopic($cloudId, $this->mapToService($request));

        return redirect()->route('admin.dictionary-topics.index')
                         ->with('success', 'Dictionary topic created.');
    }

    public function edit(string $topicCloudId)
    {
        $topic = $this->mapToView($this->service->findTopic($topicCloudId));
        if (! $topic) {
            return redirect()->route('admin.dictionary-topics.index')->with('error', 'Topic not found.');
        }

        return view('admin.dictionary-topics.form', ['topic' => $topic]);
    }

    public function update(Request $request, string $topicCloudId)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'thumbnail_url' => 'nullable|url|max:500',
            'order' => 'nullable|integer|min:0',
        ]);

        $this->service->upsertTopic($topicCloudId, $this->mapToService($request));

        return redirect()->route('admin.dictionary-topics.index')
                         ->with('success', 'Topic updated.');
    }

    public function destroy(string $topicCloudId)
    {
        $this->service->deleteTopic($topicCloudId);

        return redirect()->route('admin.dictionary-topics.index')
                         ->with('success', 'Topic deleted.');
    }
}
