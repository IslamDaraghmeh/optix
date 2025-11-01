<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Layout Test
            </h2>
            <div class="text-sm text-gray-500">
                Current Layout: {{ App\Services\LayoutService::getLayout() }}
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-semibold mb-4">{{ __('app.layout_switching_test') }}</h3>
                    <p class="mb-4">Current layout: <strong>{{ App\Services\LayoutService::getLayout() }}</strong></p>

                    <div class="space-y-2">
                        <a href="?layout=navbar"
                            class="block px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
                            {{ __('app.switch_to_navbar_layout') }}
                        </a>
                        <a href="?layout=sidebar"
                            class="block px-4 py-2 bg-primary-500 text-white rounded hover:bg-primary-600">
                            {{ __('app.switch_to_sidebar_layout') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>