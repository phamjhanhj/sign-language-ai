<!DOCTYPE html>
<html lang="vi" class="h-full bg-gray-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin') — Sign Language CMS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {"50":"#eff6ff","100":"#dbeafe","200":"#bfdbfe","300":"#93c5fd","400":"#60a5fa","500":"#3b82f6","600":"#2563eb","700":"#1d4ed8","800":"#1e40af","900":"#1e3a8a","950":"#172554"},
                        accent:  {"50":"#f0fdf4","100":"#dcfce7","200":"#bbf7d0","300":"#86efac","400":"#4ade80","500":"#22c55e","600":"#16a34a","700":"#15803d","800":"#166534","900":"#14532d","950":"#052e16"},
                    }
                }
            }
        }
    </script>
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="h-full" x-data="{ sidebarOpen: true }">
<div class="min-h-full flex">
    {{-- Sidebar --}}
    <aside class="w-64 bg-gray-900 text-white flex flex-col shrink-0 transition-all duration-200"
           :class="sidebarOpen ? 'w-64' : 'w-16'" >
        {{-- Logo --}}
        <div class="flex items-center h-16 px-4 bg-gray-950 shrink-0">
            <span class="text-xl font-bold tracking-tight" x-show="sidebarOpen" x-cloak>🤟 Sign CMS</span>
            <span class="text-xl font-bold" x-show="!sidebarOpen" x-cloak>🤟</span>
        </div>
        {{-- Nav --}}
        <nav class="flex-1 py-4 space-y-1 overflow-y-auto">
            <a href="{{ route('admin.dashboard') }}"
               class="flex items-center px-4 py-2.5 text-sm font-medium rounded-lg mx-2 transition
                      {{ request()->routeIs('admin.dashboard') ? 'bg-primary-600 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-4 0a1 1 0 01-1-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 01-1 1h-2z"/></svg>
                <span class="ml-3" x-show="sidebarOpen">Dashboard</span>
            </a>
            <a href="{{ route('admin.learning-topics.index') }}"
               class="flex items-center px-4 py-2.5 text-sm font-medium rounded-lg mx-2 transition
                      {{ request()->routeIs('admin.learning-topics.*') ? 'bg-primary-600 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                <span class="ml-3" x-show="sidebarOpen">Learning Topics</span>
            </a>
            <a href="{{ route('admin.dictionary-topics.index') }}"
               class="flex items-center px-4 py-2.5 text-sm font-medium rounded-lg mx-2 transition
                      {{ request()->routeIs('admin.dictionary-topics.*') || request()->routeIs('admin.vocabularies.*') ? 'bg-primary-600 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"/></svg>
                <span class="ml-3" x-show="sidebarOpen">Dictionary</span>
            </a>
            <a href="{{ route('admin.content.index') }}"
               class="flex items-center px-4 py-2.5 text-sm font-medium rounded-lg mx-2 transition
                      {{ request()->routeIs('admin.content.*') ? 'bg-primary-600 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                <span class="ml-3" x-show="sidebarOpen">Publish Content</span>
            </a>
        </nav>
        {{-- Toggle --}}
        <button @click="sidebarOpen = !sidebarOpen" class="p-3 text-gray-400 hover:text-white border-t border-gray-800">
            <svg class="w-5 h-5 mx-auto transition-transform" :class="sidebarOpen ? '' : 'rotate-180'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"/></svg>
        </button>
    </aside>

    {{-- Main --}}
    <div class="flex-1 flex flex-col min-w-0">
        {{-- Top bar --}}
        <header class="h-16 bg-white border-b border-gray-200 flex items-center justify-between px-6 shrink-0">
            <h1 class="text-lg font-semibold text-gray-800">@yield('heading', 'Dashboard')</h1>
            <div class="text-sm text-gray-500">Sign Language Admin</div>
        </header>
        {{-- Flash messages --}}
        @if(session('success'))
        <div class="mx-6 mt-4 p-3 bg-green-50 border border-green-200 text-green-700 rounded-lg text-sm" x-data="{show:true}" x-show="show" x-init="setTimeout(()=>show=false, 4000)" x-transition>
            ✓ {{ session('success') }}
        </div>
        @endif
        @if(session('error'))
        <div class="mx-6 mt-4 p-3 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm" x-data="{show:true}" x-show="show" x-init="setTimeout(()=>show=false, 6000)" x-transition>
            ✗ {{ session('error') }}
        </div>
        @endif
        {{-- Content --}}
        <main class="flex-1 p-6 overflow-y-auto">
            @yield('content')
        </main>
    </div>
</div>
</body>
</html>
