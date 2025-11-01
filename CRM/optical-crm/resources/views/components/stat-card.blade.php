@props(['title', 'value', 'icon', 'gradient' => 'from-blue-500 to-blue-600'])

<div class="bg-gradient-to-br {{ $gradient }} rounded-2xl p-6 text-white shadow-xl hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-1">
    <div class="flex items-center justify-between">
        <div>
            <p class="text-white/80 text-sm font-medium">{{ $title }}</p>
            <p class="text-3xl font-bold mt-2">{{ $value }}</p>
        </div>
        <div class="bg-white/20 p-3 rounded-xl">
            {!! $icon !!}
        </div>
    </div>
</div>
