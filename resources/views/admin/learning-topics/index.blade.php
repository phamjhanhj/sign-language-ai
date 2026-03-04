@extends('admin.layouts.app')
@section('title', 'Learning Topics')
@section('heading', 'Learning Topics')

@section('content')
<div class="flex items-center justify-between mb-6">
    <p class="text-sm text-gray-500">Manage learning topics and generate lessons</p>
    <a href="{{ route('admin.learning-topics.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Create Topic
    </a>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
    @if(count($topics) === 0)
        <div class="p-12 text-center">
            <svg class="w-12 h-12 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
            <p class="text-gray-500 mb-4">No learning topics yet</p>
            <a href="{{ route('admin.learning-topics.create') }}" class="text-blue-600 hover:text-blue-700 font-medium text-sm">Create your first topic →</a>
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
                                <div class="w-10 h-10 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center mr-3 text-sm font-bold">
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
                        <a href="{{ route('admin.learning-topics.show', $topic['cloud_id']) }}" class="text-blue-600 hover:text-blue-800 font-medium">View</a>
                        <a href="{{ route('admin.learning-topics.edit', $topic['cloud_id']) }}" class="text-yellow-600 hover:text-yellow-800 font-medium">Edit</a>
                        <form method="POST" action="{{ route('admin.learning-topics.destroy', $topic['cloud_id']) }}" class="inline" onsubmit="return confirm('Delete this topic?')">
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
