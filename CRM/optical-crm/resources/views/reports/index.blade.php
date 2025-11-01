<x-app-layout>
    <x-slot name="header">
        <h2 class="font-display text-2xl text-gray-800 leading-tight">
            {{ __('app.reports') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="card-primary overflow-hidden rounded-2xl">
                <div class="p-6">
                    <h3 class="text-lg font-display text-gray-900 mb-6">{{ __('app.available_reports') }}</h3>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <!-- Sales Report -->
                        <div class="bg-purple-50 p-6 rounded-xl border-2 border-purple-200 hover:shadow-xl transition-all duration-300 hover:-translate-y-1">
                            <div class="flex items-center mb-4 {{ app()->getLocale() === 'ar' ? 'flex-row-reverse' : '' }}">
                                <div class="w-12 h-12 bg-purple-500 rounded-full flex items-center justify-center shadow-lg">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z">
                                        </path>
                                    </svg>
                                </div>
                                <h4 class="text-lg font-display text-purple-900 {{ app()->getLocale() === 'ar' ? 'mr-3' : 'ml-3' }}">
                                    {{ __('app.sales_report') }}
                                </h4>
                            </div>
                            <p class="text-purple-700 mb-4 leading-relaxed">
                                {{ __('app.sales_report_description') }}
                            </p>
                            <a href="{{ route('reports.sales') }}"
                                class="bg-purple-500 hover:bg-purple-600 text-white px-6 py-3 rounded-xl font-medium shadow-lg hover:shadow-xl transition-all duration-300 inline-block transform hover:scale-105">
                                {{ __('app.view_sales_report') }}
                            </a>
                        </div>

                        <!-- Patient Report -->
                        <div class="bg-teal-50 p-6 rounded-xl border-2 border-teal-200 hover:shadow-xl transition-all duration-300 hover:-translate-y-1">
                            <div class="flex items-center mb-4 {{ app()->getLocale() === 'ar' ? 'flex-row-reverse' : '' }}">
                                <div class="w-12 h-12 bg-teal-500 rounded-full flex items-center justify-center shadow-lg">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z">
                                        </path>
                                    </svg>
                                </div>
                                <h4 class="text-lg font-display text-teal-900 {{ app()->getLocale() === 'ar' ? 'mr-3' : 'ml-3' }}">
                                    {{ __('app.patient_report') }}
                                </h4>
                            </div>
                            <p class="text-teal-700 mb-4 leading-relaxed">
                                {{ __('app.patient_report_description') }}
                            </p>
                            <a href="{{ route('reports.patients') }}"
                                class="bg-teal-500 hover:bg-teal-600 text-white px-6 py-3 rounded-xl font-medium shadow-lg hover:shadow-xl transition-all duration-300 inline-block transform hover:scale-105">
                                {{ __('app.view_patient_report') }}
                            </a>
                        </div>

                        <!-- Glasses Report -->
                        <div class="bg-green-50 p-6 rounded-xl border-2 border-green-200 hover:shadow-xl transition-all duration-300 hover:-translate-y-1">
                            <div class="flex items-center mb-4 {{ app()->getLocale() === 'ar' ? 'flex-row-reverse' : '' }}">
                                <div class="w-12 h-12 bg-primary-500 rounded-full flex items-center justify-center shadow-lg">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <h4 class="text-lg font-display text-green-900 {{ app()->getLocale() === 'ar' ? 'mr-3' : 'ml-3' }}">
                                    {{ __('app.glasses_report') }}
                                </h4>
                            </div>
                            <p class="text-primary-700 mb-4 leading-relaxed">
                                {{ __('app.glasses_report_description') }}
                            </p>
                            <a href="{{ route('reports.glasses') }}"
                                class="bg-primary-500 hover:bg-primary-600 text-white px-6 py-3 rounded-xl font-medium shadow-lg hover:shadow-xl transition-all duration-300 inline-block transform hover:scale-105">
                                {{ __('app.view_glasses_report') }}
                            </a>
                        </div>
                    </div>

                    <!-- Quick Stats -->
                    <div class="mt-8 pt-8 border-t-2 border-gray-200">
                        <h4 class="text-lg font-display text-gray-900 mb-6">{{ __('app.quick_statistics') }}</h4>
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                            <div class="card-secondary p-6 rounded-xl text-center hover:shadow-xl transition-all duration-300 hover:-translate-y-1">
                                <div class="text-3xl font-bold text-purple-600 mb-2">{{ \App\Models\Sale::count() }}</div>
                                <div class="text-sm font-medium text-gray-600">{{ __('app.total_sales') }}</div>
                            </div>
                            <div class="card-secondary p-6 rounded-xl text-center hover:shadow-xl transition-all duration-300 hover:-translate-y-1">
                                <div class="text-3xl font-bold text-teal-600 mb-2">{{ \App\Models\Patient::count() }}</div>
                                <div class="text-sm font-medium text-gray-600">{{ __('app.total_patients') }}</div>
                            </div>
                            <div class="card-secondary p-6 rounded-xl text-center hover:shadow-xl transition-all duration-300 hover:-translate-y-1">
                                <div class="text-3xl font-bold text-primary-600 mb-2">{{ \App\Models\Glass::count() }}</div>
                                <div class="text-sm font-medium text-gray-600">{{ __('app.glasses_orders') }}</div>
                            </div>
                            <div class="card-secondary p-6 rounded-xl text-center hover:shadow-xl transition-all duration-300 hover:-translate-y-1">
                                <div class="text-3xl font-bold text-blue-600 mb-2">{{ \App\Models\Exam::count() }}</div>
                                <div class="text-sm font-medium text-gray-600">{{ __('app.eye_exams') }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
