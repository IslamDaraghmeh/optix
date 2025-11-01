@props(['type' => 'success', 'message' => ''])

@if(session($type) || $message)
    <div x-data="{ show: true }" x-show="show" x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 transform scale-90" x-transition:enter-end="opacity-100 transform scale-100"
        x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 transform scale-100"
        x-transition:leave-end="opacity-0 transform scale-90" class="fixed top-4 right-4 z-50 max-w-sm w-full">
        <div
            class="bg-white rounded-xl shadow-2xl border-l-4 {{ $type === 'success' ? 'border-primary-500' : ($type === 'error' ? 'border-red-500' : 'border-blue-500') }} p-4">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    @if($type === 'success')
                        <svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    @elseif($type === 'error')
                        <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                            </path>
                        </svg>
                    @else
                        <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    @endif
                </div>
                <div class="ml-3 flex-1">
                    <p
                        class="text-sm font-medium {{ $type === 'success' ? 'text-primary-800' : ($type === 'error' ? 'text-red-800' : 'text-blue-800') }}">
                        {{ $message ?: session($type) }}
                    </p>
                </div>
                <div class="ml-4 flex-shrink-0">
                    <button @click="show = false"
                        class="inline-flex {{ $type === 'success' ? 'text-green-400 hover:text-primary-600' : ($type === 'error' ? 'text-red-400 hover:text-red-600' : 'text-blue-400 hover:text-blue-600') }} focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                            </path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>
@endif
