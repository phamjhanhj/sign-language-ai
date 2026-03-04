@extends('admin.layouts.app')
@section('title', 'Publish Content')
@section('heading', 'Publish Content')

@section('content')
<div class="max-w-2xl">
    {{-- Current version --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">Current Version</h2>
        <div class="flex items-center space-x-6">
            <div class="text-center">
                <p class="text-4xl font-bold text-blue-600">{{ $meta['version'] ?? 0 }}</p>
                <p class="text-xs text-gray-500 mt-1">Version</p>
            </div>
            @if(!empty($meta['published_at']))
            <div>
                <p class="text-sm text-gray-700"><span class="font-medium">Published:</span> {{ $meta['published_at'] }}</p>
                @if(!empty($meta['checksum']))
                    <p class="text-xs text-gray-400 font-mono mt-1">Checksum: {{ substr($meta['checksum'], 0, 16) }}…</p>
                @endif
            </div>
            @else
            <p class="text-sm text-gray-500">No content published yet</p>
            @endif
        </div>
    </div>

    {{-- Publish --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6" x-data="{ confirming: false }">
        <h2 class="text-lg font-semibold text-gray-800 mb-2">Publish New Version</h2>
        <p class="text-sm text-gray-500 mb-5">
            This will snapshot all current learning topics, lessons, dictionary topics, and vocabularies
            into a new version. The Flutter app will download this data on next sync.
        </p>

        <div x-show="!confirming">
            <button @click="confirming = true" class="px-5 py-2.5 text-sm font-medium text-white bg-purple-600 rounded-lg hover:bg-purple-700 transition">
                🚀 Publish Now
            </button>
        </div>

        <div x-show="confirming" x-cloak x-transition class="p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
            <p class="text-sm text-yellow-800 font-medium mb-3">
                ⚠️ Are you sure? This will create version <strong>{{ ($meta['version'] ?? 0) + 1 }}</strong> and make it available to all users.
            </p>
            <form method="POST" action="{{ route('admin.content.publish') }}" class="flex space-x-3">
                @csrf
                <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-purple-600 rounded-lg hover:bg-purple-700 transition">
                    Yes, Publish
                </button>
                <button type="button" @click="confirming = false" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                    Cancel
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
