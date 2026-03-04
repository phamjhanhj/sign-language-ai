@extends('admin.layouts.app')
@section('title', $topic['title'] ?? 'Topic Detail')
@section('heading')
    <nav class="flex items-center space-x-2 text-sm">
        <a href="{{ route('admin.learning-topics.index') }}" class="text-gray-500 hover:text-gray-700">Learning Topics</a>
        <span class="text-gray-400">/</span>
        <span>{{ $topic['title'] ?? 'Detail' }}</span>
    </nav>
@endsection

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    {{-- Topic info --}}
    <div class="lg:col-span-1">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            @if(!empty($topic['thumbnail_url']))
                <img src="{{ $topic['thumbnail_url'] }}" class="w-full h-40 object-cover rounded-lg mb-4" alt="">
            @endif
            <h2 class="text-lg font-semibold text-gray-900 mb-2">{{ $topic['title'] ?? 'Untitled' }}</h2>
            <p class="text-sm text-gray-500 mb-4">{{ $topic['description'] ?? 'No description' }}</p>
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <dt class="text-gray-500">Order</dt>
                    <dd class="font-medium text-gray-900">{{ $topic['order'] ?? 0 }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500">Cloud ID</dt>
                    <dd class="font-mono text-xs text-gray-400">{{ $topic['cloud_id'] }}</dd>
                </div>
            </dl>
            <div class="mt-5 flex space-x-2">
                <a href="{{ route('admin.learning-topics.edit', $topic['cloud_id']) }}" class="flex-1 text-center px-3 py-2 text-sm font-medium bg-yellow-50 text-yellow-700 rounded-lg hover:bg-yellow-100 transition">Edit</a>
                <form method="POST" action="{{ route('admin.learning-topics.destroy', $topic['cloud_id']) }}" class="flex-1" onsubmit="return confirm('Delete?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="w-full px-3 py-2 text-sm font-medium bg-red-50 text-red-700 rounded-lg hover:bg-red-100 transition">Delete</button>
                </form>
            </div>
        </div>

        {{-- Generate Lessons --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mt-6" x-data="{ open: false }">
            <h3 class="text-sm font-semibold text-gray-800 mb-3">Generate 8 Lessons</h3>
            <p class="text-xs text-gray-500 mb-3">Auto-generate lessons from vocabulary and sentences. This will replace all existing lessons.</p>
            <button @click="open = !open" class="w-full px-3 py-2 text-sm font-medium bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition">
                Generate Lessons
            </button>

            <div x-show="open" x-cloak x-transition class="mt-4">
                <form method="POST" action="{{ route('admin.learning-topics.generate-lessons', $topic['cloud_id']) }}" id="generateForm">
                    @csrf
                    <div class="space-y-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Vocabularies (JSON array)</label>
                            <textarea name="vocabularies" rows="4"
                                      class="w-full border border-gray-300 rounded-lg px-3 py-2 text-xs font-mono focus:ring-2 focus:ring-purple-500 outline-none"
                                      placeholder='[{"word":"xin chào","video_url":"...","thumbnail_url":"..."}]'>{{ old('vocabularies', '[]') }}</textarea>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Sentences (JSON array)</label>
                            <textarea name="sentences" rows="4"
                                      class="w-full border border-gray-300 rounded-lg px-3 py-2 text-xs font-mono focus:ring-2 focus:ring-purple-500 outline-none"
                                      placeholder='[{"text":"xin chào bạn","words":["xin","chào","bạn"],"video_url":"..."}]'>{{ old('sentences', '[]') }}</textarea>
                        </div>
                        <button type="submit" class="w-full px-3 py-2 text-sm font-medium bg-purple-700 text-white rounded-lg hover:bg-purple-800 transition">
                            ⚡ Generate Now
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Lessons --}}
    <div class="lg:col-span-2">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200">
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-800">Lessons ({{ count($lessons) }})</h3>
            </div>
            @if(count($lessons) === 0)
                <div class="p-12 text-center">
                    <p class="text-gray-500 text-sm">No lessons yet. Use "Generate Lessons" to create 8 auto-generated lessons.</p>
                </div>
            @else
                <div class="divide-y divide-gray-200">
                    @foreach($lessons as $lesson)
                    <div class="p-4 hover:bg-gray-50" x-data="{ expanded: false }">
                        <div class="flex items-center justify-between cursor-pointer" @click="expanded = !expanded">
                            <div class="flex items-center space-x-3">
                                <span class="w-8 h-8 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center text-sm font-bold">
                                    {{ $lesson['order'] ?? $loop->iteration }}
                                </span>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ $lesson['title'] ?? 'Lesson '.$loop->iteration }}</p>
                                    <p class="text-xs text-gray-500">{{ $lesson['type'] ?? '' }} · {{ count($lesson['questions'] ?? []) }} questions</p>
                                </div>
                            </div>
                            <svg class="w-5 h-5 text-gray-400 transition-transform" :class="expanded && 'rotate-180'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                        <div x-show="expanded" x-cloak x-transition class="mt-3 ml-11">
                            @foreach(($lesson['questions'] ?? []) as $qi => $q)
                            <div class="mb-2 p-3 bg-gray-50 rounded-lg text-xs">
                                <span class="inline-block px-2 py-0.5 rounded text-white text-[10px] font-bold uppercase
                                    {{ ($q['type'] ?? '') === 'learn' ? 'bg-green-500' : (($q['type'] ?? '') === 'choice' ? 'bg-blue-500' : 'bg-orange-500') }}">
                                    {{ $q['type'] ?? '?' }}
                                </span>
                                @if(!empty($q['word']))
                                    <span class="ml-2 text-gray-700 font-medium">{{ $q['word'] }}</span>
                                @endif
                                @if(!empty($q['sentence_text']))
                                    <span class="ml-2 text-gray-700">{{ $q['sentence_text'] }}</span>
                                @endif
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
