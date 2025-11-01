@props(['title', 'description' => '', 'icon' => null, 'buttonText' => null, 'buttonAction' => null])

<div class="flex justify-between items-center animate-fadeInUp">
    <div class="flex items-center space-x-4">
        @if($icon)
        <div class="bg-white/20 backdrop-blur-sm p-3 rounded-xl border border-white/30 shadow-lg">
            {!! $icon !!}
        </div>
        @endif
        <div>
            <h2 class="font-display text-3xl text-white leading-tight drop-shadow-lg">
                {{ $title }}
            </h2>
            @if($description)
            <p class="text-primary-100 text-sm mt-1">{{ $description }}</p>
            @endif
        </div>
    </div>

    @if($buttonText && $buttonAction)
    <button onclick="{{ $buttonAction }}"
        class="bg-white/95 hover:bg-white text-primary-700 px-6 py-3 rounded-xl font-semibold shadow-xl hover:shadow-2xl transition-all duration-300 transform hover:scale-105 border border-white/50 backdrop-blur-sm">
        <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
        </svg>
        {{ $buttonText }}
    </button>
    @endif

    {{ $slot }}
</div>
