@extends('admin.layouts.app')
@section('title', 'Vocabularies — ' . ($topicTitle ?? 'Topic'))
@section('heading')
    <nav class="flex items-center space-x-2 text-sm">
        <a href="{{ route('admin.dictionary-topics.index') }}" class="text-gray-500 hover:text-gray-700">Dictionary Topics</a>
        <span class="text-gray-400">/</span>
        <span>{{ $topicTitle ?? $topicCloudId }}</span>
        <span class="text-gray-400">/</span>
        <span>Vocabularies</span>
    </nav>
@endsection

@section('content')
<div class="flex items-center justify-between mb-6">
    <p class="text-sm text-gray-500">{{ count($vocabularies) }} vocabularies in this topic</p>
    <a href="{{ route('admin.vocabularies.create', $topicCloudId) }}" class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Add Vocabulary
    </a>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
    @if(count($vocabularies) === 0)
        <div class="p-12 text-center">
            <p class="text-gray-500 mb-4">No vocabularies yet</p>
            <a href="{{ route('admin.vocabularies.create', $topicCloudId) }}" class="text-green-600 hover:text-green-700 font-medium text-sm">Add your first vocabulary →</a>
        </div>
    @else
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Word</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Video</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($vocabularies as $vocab)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            @if(!empty($vocab['thumbnail_url']))
                                <img src="{{ $vocab['thumbnail_url'] }}" class="w-10 h-10 rounded-lg object-cover mr-3" alt="">
                            @else
                                <div class="w-10 h-10 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center mr-3 text-sm font-bold">
                                    {{ strtoupper(substr($vocab['word'] ?? '?', 0, 1)) }}
                                </div>
                            @endif
                            <div>
                                <div class="text-sm font-medium text-gray-900">{{ $vocab['word'] ?? 'Untitled' }}</div>
                                <div class="text-xs text-gray-400 font-mono">{{ $vocab['cloud_id'] ?? '' }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        @if(!empty($vocab['video_url']))
                            <a href="{{ $vocab['video_url'] }}" target="_blank" class="text-blue-600 hover:text-blue-800 text-xs">View video ↗</a>
                        @else
                            <span class="text-gray-400 text-xs">No video</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $vocab['order'] ?? '-' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm space-x-2">
                        <a href="{{ route('admin.vocabularies.edit', [$topicCloudId, $vocab['cloud_id']]) }}" class="text-yellow-600 hover:text-yellow-800 font-medium">Edit</a>
                        <form method="POST" action="{{ route('admin.vocabularies.destroy', [$topicCloudId, $vocab['cloud_id']]) }}" class="inline" onsubmit="return confirm('Delete this vocabulary?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-red-600 hover:text-red-800 font-medium">Delete</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
@endsection
