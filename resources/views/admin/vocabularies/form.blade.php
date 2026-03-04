@extends('admin.layouts.app')
@section('title', isset($vocab) ? 'Edit Vocabulary' : 'Add Vocabulary')
@section('heading')
    <nav class="flex items-center space-x-2 text-sm">
        <a href="{{ route('admin.dictionary-topics.index') }}" class="text-gray-500 hover:text-gray-700">Dictionary</a>
        <span class="text-gray-400">/</span>
        <a href="{{ route('admin.vocabularies.index', $topicCloudId) }}" class="text-gray-500 hover:text-gray-700">{{ $topicTitle ?? $topicCloudId }}</a>
        <span class="text-gray-400">/</span>
        <span>{{ isset($vocab) ? 'Edit' : 'Add' }}</span>
    </nav>
@endsection

@section('content')
<div class="max-w-2xl">
    {{-- Example data buttons (only on create) --}}
    @unless(isset($vocab))
    <div class="mb-4 p-4 bg-emerald-50 border border-emerald-200 rounded-xl" x-data="{ examples: [
        { word: 'Xin chào', video_url: 'https://storage.googleapis.com/sign-lang/videos/xin-chao.mp4', thumbnail_url: 'https://placehold.co/400x300/22c55e/white?text=Xin+Chao', order: 1 },
        { word: 'Cảm ơn', video_url: 'https://storage.googleapis.com/sign-lang/videos/cam-on.mp4', thumbnail_url: 'https://placehold.co/400x300/3b82f6/white?text=Cam+On', order: 2 },
        { word: 'Xin lỗi', video_url: 'https://storage.googleapis.com/sign-lang/videos/xin-loi.mp4', thumbnail_url: 'https://placehold.co/400x300/f97316/white?text=Xin+Loi', order: 3 },
        { word: 'Tạm biệt', video_url: 'https://storage.googleapis.com/sign-lang/videos/tam-biet.mp4', thumbnail_url: 'https://placehold.co/400x300/a855f7/white?text=Tam+Biet', order: 4 },
        { word: 'Bạn', video_url: 'https://storage.googleapis.com/sign-lang/videos/ban.mp4', thumbnail_url: 'https://placehold.co/400x300/ef4444/white?text=Ban', order: 5 },
        { word: 'Vui', video_url: 'https://storage.googleapis.com/sign-lang/videos/vui.mp4', thumbnail_url: 'https://placehold.co/400x300/eab308/white?text=Vui', order: 6 },
    ] }">
        <p class="text-sm font-medium text-emerald-800 mb-2">📝 Dữ liệu mẫu — chọn 1 từ vựng để tự điền:</p>
        <div class="flex flex-wrap gap-2">
            <template x-for="(ex, i) in examples" :key="i">
                <button type="button"
                        @click="document.getElementById('word').value = ex.word;
                                document.getElementById('video_url').value = ex.video_url;
                                document.getElementById('thumbnail_url').value = ex.thumbnail_url;
                                document.getElementById('order').value = ex.order;"
                        class="px-3 py-1.5 text-xs font-medium bg-white text-emerald-700 border border-emerald-300 rounded-lg hover:bg-emerald-100 transition"
                        x-text="ex.word"></button>
            </template>
        </div>
    </div>
    @endunless

    <form method="POST"
          action="{{ isset($vocab) ? route('admin.vocabularies.update', [$topicCloudId, $vocab['cloud_id']]) : route('admin.vocabularies.store', $topicCloudId) }}">
        @csrf
        @if(isset($vocab)) @method('PUT') @endif

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 divide-y divide-gray-200">
            <div class="p-6 space-y-5">
                <div>
                    <label for="word" class="block text-sm font-medium text-gray-700 mb-1">Word <span class="text-red-500">*</span></label>
                    <input type="text" name="word" id="word"
                           value="{{ old('word', $vocab['word'] ?? '') }}"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none"
                           required>
                    @error('word') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="video_url" class="block text-sm font-medium text-gray-700 mb-1">Video URL</label>
                    <input type="url" name="video_url" id="video_url"
                           value="{{ old('video_url', $vocab['video_url'] ?? '') }}"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none"
                           placeholder="https://storage.googleapis.com/...">
                </div>

                <div>
                    <label for="thumbnail_url" class="block text-sm font-medium text-gray-700 mb-1">Thumbnail URL</label>
                    <input type="url" name="thumbnail_url" id="thumbnail_url"
                           value="{{ old('thumbnail_url', $vocab['thumbnail_url'] ?? '') }}"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none"
                           placeholder="https://example.com/thumb.png">
                </div>

                <div>
                    <label for="order" class="block text-sm font-medium text-gray-700 mb-1">Display Order</label>
                    <input type="number" name="order" id="order"
                           value="{{ old('order', $vocab['order'] ?? 0) }}"
                           class="w-32 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none">
                </div>
            </div>

            <div class="p-6 flex items-center justify-end space-x-3 bg-gray-50 rounded-b-xl">
                <a href="{{ route('admin.vocabularies.index', $topicCloudId) }}" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">Cancel</a>
                <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 transition">
                    {{ isset($vocab) ? 'Update Vocabulary' : 'Add Vocabulary' }}
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
