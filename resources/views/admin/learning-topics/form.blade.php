@extends('admin.layouts.app')
@section('title', isset($topic) ? 'Edit Learning Topic' : 'Create Learning Topic')
@section('heading')
    <nav class="flex items-center space-x-2 text-sm">
        <a href="{{ route('admin.learning-topics.index') }}" class="text-gray-500 hover:text-gray-700">Learning Topics</a>
        <span class="text-gray-400">/</span>
        <span>{{ isset($topic) ? 'Edit' : 'Create' }}</span>
    </nav>
@endsection

@section('content')
<div class="max-w-2xl">
    {{-- Example data button (only on create) --}}
    @unless(isset($topic))
    <div class="mb-4 p-4 bg-blue-50 border border-blue-200 rounded-xl" x-data="{ examples: [
        { title: 'Chào hỏi cơ bản', description: 'Học các từ và câu chào hỏi thường dùng trong ngôn ngữ ký hiệu', thumbnail_url: 'https://placehold.co/400x300/3b82f6/white?text=Chao+Hoi', order: 1 },
        { title: 'Gia đình', description: 'Từ vựng về các thành viên trong gia đình: bố, mẹ, anh, chị, em', thumbnail_url: 'https://placehold.co/400x300/22c55e/white?text=Gia+Dinh', order: 2 },
        { title: 'Số đếm 1-10', description: 'Học đếm số từ 1 đến 10 bằng ngôn ngữ ký hiệu', thumbnail_url: 'https://placehold.co/400x300/a855f7/white?text=So+Dem', order: 3 },
        { title: 'Màu sắc', description: 'Các từ vựng về màu sắc: đỏ, xanh, vàng, trắng, đen', thumbnail_url: 'https://placehold.co/400x300/f97316/white?text=Mau+Sac', order: 4 },
        { title: 'Trường học', description: 'Từ vựng liên quan đến trường học, lớp học, giáo viên, bạn bè', thumbnail_url: 'https://placehold.co/400x300/ef4444/white?text=Truong+Hoc', order: 5 },
    ] }">
        <p class="text-sm font-medium text-blue-800 mb-2">📝 Dữ liệu mẫu — chọn 1 ví dụ để tự điền:</p>
        <div class="flex flex-wrap gap-2">
            <template x-for="(ex, i) in examples" :key="i">
                <button type="button"
                        @click="document.getElementById('title').value = ex.title;
                                document.getElementById('description').value = ex.description;
                                document.getElementById('thumbnail_url').value = ex.thumbnail_url;
                                document.getElementById('order').value = ex.order;"
                        class="px-3 py-1.5 text-xs font-medium bg-white text-blue-700 border border-blue-300 rounded-lg hover:bg-blue-100 transition"
                        x-text="ex.title"></button>
            </template>
        </div>
    </div>
    @endunless

    <form method="POST"
          action="{{ isset($topic) ? route('admin.learning-topics.update', $topic['cloud_id']) : route('admin.learning-topics.store') }}">
        @csrf
        @if(isset($topic)) @method('PUT') @endif

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 divide-y divide-gray-200">
            <div class="p-6 space-y-5">
                {{-- Title --}}
                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Title <span class="text-red-500">*</span></label>
                    <input type="text" name="title" id="title"
                           value="{{ old('title', $topic['title'] ?? '') }}"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"
                           required>
                    @error('title') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Description --}}
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea name="description" id="description" rows="3"
                              class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">{{ old('description', $topic['description'] ?? '') }}</textarea>
                </div>

                {{-- Thumbnail URL --}}
                <div>
                    <label for="thumbnail_url" class="block text-sm font-medium text-gray-700 mb-1">Thumbnail URL</label>
                    <input type="url" name="thumbnail_url" id="thumbnail_url"
                           value="{{ old('thumbnail_url', $topic['thumbnail_url'] ?? '') }}"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"
                           placeholder="https://example.com/image.png">
                </div>

                {{-- Order --}}
                <div>
                    <label for="order" class="block text-sm font-medium text-gray-700 mb-1">Display Order</label>
                    <input type="number" name="order" id="order"
                           value="{{ old('order', $topic['order'] ?? 0) }}"
                           class="w-32 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                </div>
            </div>

            <div class="p-6 flex items-center justify-end space-x-3 bg-gray-50 rounded-b-xl">
                <a href="{{ route('admin.learning-topics.index') }}" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">Cancel</a>
                <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition">
                    {{ isset($topic) ? 'Update Topic' : 'Create Topic' }}
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
