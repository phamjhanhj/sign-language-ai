@extends('admin.layouts.app')
@section('title', 'Dictionary Topics')
@section('heading', 'Dictionary Topics')

@section('content')
<div class="flex items-center justify-between mb-6">
    <p class="text-sm text-gray-500">Manage dictionary topics and vocabularies</p>
    <a href="{{ route('admin.dictionary-topics.create') }}" class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Create Topic
    </a>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
    @if(count($topics) === 0)
        <div class="p-12 text-center">
            <svg class="w-12 h-12 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"/></svg>
            <p class="text-gray-500 mb-4">No dictionary topics yet</p>
            <a href="{{ route('admin.dictionary-topics.create') }}" class="text-green-600 hover:text-green-700 font-medium text-sm">Create your first topic →</a>
        </div>
    @else
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($topics as $topic)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            @if(!empty($topic['thumbnail_url']))
                                <img src="{{ $topic['thumbnail_url'] }}" class="w-10 h-10 rounded-lg object-cover mr-3" alt="">
                            @else
                                <div class="w-10 h-10 rounded-lg bg-green-100 text-green-600 flex items-center justify-center mr-3 text-sm font-bold">
                                    {{ strtoupper(substr($topic['title'] ?? '?', 0, 1)) }}
                                </div>
                            @endif
                            <div>
                                <div class="text-sm font-medium text-gray-900">{{ $topic['title'] ?? 'Untitled' }}</div>
                                @if(!empty($topic['description']))
                                    <div class="text-xs text-gray-500 truncate max-w-xs">{{ $topic['description'] }}</div>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $topic['order'] ?? '-' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-400 font-mono">{{ $topic['cloud_id'] ?? '-' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm space-x-2">
                        <a href="{{ route('admin.vocabularies.index', $topic['cloud_id']) }}" class="text-green-600 hover:text-green-800 font-medium">Vocabs</a>
                        <a href="{{ route('admin.dictionary-topics.edit', $topic['cloud_id']) }}" class="text-yellow-600 hover:text-yellow-800 font-medium">Edit</a>
                        <form method="POST" action="{{ route('admin.dictionary-topics.destroy', $topic['cloud_id']) }}" class="inline" onsubmit="return confirm('Delete this topic and all its vocabularies?')">
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
