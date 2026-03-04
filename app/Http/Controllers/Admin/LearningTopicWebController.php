<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Firestore\LearningTopicService;
use Illuminate\Http\Request;

class LearningTopicWebController extends Controller
{
    public function __construct(private readonly LearningTopicService $service) {}

    public function index()
    {
        return view('admin.learning-topics.index', [
            'topics' => $this->service->list(),
        ]);
    }

    public function create()
    {
        return view('admin.learning-topics.form');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'thumbnail_url' => 'nullable|url|max:500',
            'order' => 'nullable|integer|min:0',
        ]);

        $cloudId = uniqid('lt_');
        $this->service->upsert($cloudId, $request->only('title', 'description', 'thumbnail_url', 'order'));

        return redirect()->route('admin.learning-topics.show', $cloudId)
                         ->with('success', 'Learning topic created successfully.');
    }

    public function show(string $topicCloudId)
    {
        $topic = $this->service->find($topicCloudId);
        if (! $topic) {
            return redirect()->route('admin.learning-topics.index')->with('error', 'Topic not found.');
        }

        return view('admin.learning-topics.show', [
            'topic'   => $topic,
            'lessons' => $this->service->listLessons($topicCloudId),
        ]);
    }

    public function edit(string $topicCloudId)
    {
        $topic = $this->service->find($topicCloudId);
        if (! $topic) {
            return redirect()->route('admin.learning-topics.index')->with('error', 'Topic not found.');
        }

        return view('admin.learning-topics.form', ['topic' => $topic]);
    }

    public function update(Request $request, string $topicCloudId)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'thumbnail_url' => 'nullable|url|max:500',
            'order' => 'nullable|integer|min:0',
        ]);

        $this->service->upsert($topicCloudId, $request->only('title', 'description', 'thumbnail_url', 'order'));

        return redirect()->route('admin.learning-topics.show', $topicCloudId)
                         ->with('success', 'Topic updated.');
    }

    public function destroy(string $topicCloudId)
    {
        $this->service->delete($topicCloudId);

        return redirect()->route('admin.learning-topics.index')
                         ->with('success', 'Topic deleted.');
    }

    public function generateLessons(Request $request, string $topicCloudId)
    {
        $vocabularies = json_decode($request->input('vocabularies', '[]'), true);
        $sentences    = json_decode($request->input('sentences', '[]'), true);

        if (! is_array($vocabularies) || ! is_array($sentences)) {
            return back()->with('error', 'Invalid JSON format for vocabularies or sentences.');
        }

        try {
            $result = $this->service->generateLessons($topicCloudId, $vocabularies, $sentences);

            if (isset($result['error'])) {
                return back()->with('error', $result['message'] ?? 'Failed to generate lessons.');
            }

            return redirect()->route('admin.learning-topics.show', $topicCloudId)
                             ->with('success', $result['message'] ?? 'Lessons generated.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Error: '.$e->getMessage());
        }
    }
}
