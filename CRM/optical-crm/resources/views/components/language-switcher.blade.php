@props(['currentLocale' => app()->getLocale()])

<div class="relative" x-data="{ open: false }">
    <button @click="open = !open"
        class="flex items-center space-x-2 px-3 py-2 rounded-lg text-sm font-medium text-gray-700 hover:text-teal-600 hover:bg-teal-50 border border-transparent transition-all duration-200">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129">
            </path>
        </svg>
        <span class="hidden sm:inline">
            @if($currentLocale === 'ar')
                العربية
            @else
                English
            @endif
        </span>
        <svg class="w-4 h-4 transition-transform duration-200" :class="{ 'rotate-180': open }" fill="none"
            stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
        </svg>
    </button>

    <div x-show="open" @click.away="open = false" x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="transform opacity-0 scale-95" x-transition:enter-end="transform opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75" x-transition:leave-start="transform opacity-100 scale-100"
        x-transition:leave-end="transform opacity-0 scale-95"
        class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 py-1 z-50">

        <a href="{{ route('locale.switch', 'en') }}"
            class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-teal-50 hover:text-teal-600 transition-colors duration-200 {{ $currentLocale === 'en' ? 'bg-teal-50 text-teal-600' : '' }}">
            <span class="text-lg mr-3">🇺🇸</span>
            <span class="font-medium">English</span>
            @if($currentLocale === 'en')
                <svg class="w-4 h-4 ml-auto text-teal-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                        clip-rule="evenodd"></path>
                </svg>
            @endif
        </a>

        <a href="{{ route('locale.switch', 'ar') }}"
            class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-teal-50 hover:text-teal-600 transition-colors duration-200 {{ $currentLocale === 'ar' ? 'bg-teal-50 text-teal-600' : '' }}">
            <span class="text-lg mr-3">🇸🇦</span>
            <span class="font-medium">العربية</span>
            @if($currentLocale === 'ar')
                <svg class="w-4 h-4 ml-auto text-teal-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                        clip-rule="evenodd"></path>
                </svg>
            @endif
        </a>
    </div>
</div>