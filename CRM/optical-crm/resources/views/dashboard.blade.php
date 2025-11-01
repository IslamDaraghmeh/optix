<x-app-layout>
    <x-slot name="header">
        <div class=" rounded-2xl p-8 shadow-lg">
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="font-display text-3xl ">
                        {{ __('app.name') }} - {{ __('app.dashboard') }}
                    </h2>
                    <p class=" mt-1">{{ __('app.welcome_back') }}</p>
                </div>
                <div class="text-right">
                    <p class="  text-sm">{{ now()->format('l, F j, Y') }}</p>
                    <p class="  font-semibold">{{ now()->format('g:i A') }}</p>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Today's Statistics -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-6 mb-8 animate-fadeInUp">
                <div class="card-primary p-6 hover:shadow-2xl transition-all duration-300 hover-lift">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-primary-500 rounded-xl flex items-center justify-center shadow-lg">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z">
                                    </path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-600 truncate">{{ __('app.todays_patients') }}
                                </dt>
                                <dd class="text-2xl font-bold text-gray-900">{{ $todayStats['patients'] ?? 0 }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>

                <div class="card-secondary p-6 hover:shadow-2xl transition-all duration-300 hover-lift">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div
                                class="w-12 h-12 bg-secondary-500 rounded-xl flex items-center justify-center shadow-lg">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                                    </path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-600 truncate">{{ __('app.todays_exams') }}</dt>
                                <dd class="text-2xl font-bold text-gray-900">{{ $todayStats['exams'] ?? 0 }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>

                <div class="card-accent p-6 hover:shadow-2xl transition-all duration-300 hover-lift">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-accent-500 rounded-xl flex items-center justify-center shadow-lg">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-600 truncate">{{ __('app.glasses_ready') }}
                                </dt>
                                <dd class="text-2xl font-bold text-gray-900">{{ $todayStats['glasses_ready'] ?? 0 }}
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>

                <div class="card-warm p-6 hover:shadow-2xl transition-all duration-300 hover-lift">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-primary-400 rounded-xl flex items-center justify-center shadow-lg">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z">
                                    </path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-600 truncate">{{ __('app.todays_sales') }}</dt>
                                <dd class="text-2xl font-bold text-gray-900">
                                    {{ __('app.currency_symbol') }}{{ number_format($todayStats['revenue'] ?? 0, 2) }}
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>

                <div class="card-cool p-6 hover:shadow-2xl transition-all duration-300 hover-lift">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-primary-700 rounded-xl flex items-center justify-center shadow-lg">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1">
                                    </path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-600 truncate">{{ __('app.outstanding') }}</dt>
                                <dd class="text-2xl font-bold text-gray-900">
                                    {{ __('app.currency_symbol') }}{{ number_format($outstandingPaymentsTotal ?? 0, 2) }}
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>

                <div class="card-success p-6 hover:shadow-2xl transition-all duration-300 hover-lift">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div
                                class="w-12 h-12 bg-secondary-500 rounded-xl flex items-center justify-center shadow-lg">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                                    </path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-600 truncate">{{ __('app.expenses') }}</dt>
                                <dd class="text-2xl font-bold text-gray-900">
                                    {{ __('app.currency_symbol') }}{{ number_format($todayStats['expense_amount'] ?? 0, 2) }}
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 mb-8 animate-slideInRight">
                <div class="lg:col-span-2 card-secondary p-6">
                    <h3 class="text-lg font-display text-gray-900 mb-4">{{ __('app.quick_actions') }}</h3>
                    <div class="grid grid-cols-2 lg:grid-cols-3 gap-4">
                        <button onclick="openModal('patientModal')"
                            class="flex items-center p-4 bg-primary-500 rounded-xl hover:shadow-lg transition-all duration-200 group w-full text-left shadow-sm hover:shadow-md text-white">
                            <div
                                class="w-10 h-10 bg-white/20 rounded-lg flex items-center justify-center mr-3 group-hover:scale-110 transition-transform shadow-lg">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                            </div>
                            <div>
                                <p class="font-medium text-white">{{ __('app.add_patient') }}</p>
                                <p class="text-sm text-white/80">{{ __('app.new_patient_registration') }}</p>
                            </div>
                        </button>

                        <button onclick="openModal('examModal')"
                            class="flex items-center p-4 bg-secondary-500 rounded-xl hover:shadow-lg transition-all duration-200 group w-full text-left shadow-sm hover:shadow-md text-white">
                            <div
                                class="w-10 h-10 bg-white/20 rounded-lg flex items-center justify-center mr-3 group-hover:scale-110 transition-transform shadow-lg">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                                    </path>
                                </svg>
                            </div>
                            <div>
                                <p class="font-medium text-white">{{ __('app.new_exam') }}</p>
                                <p class="text-sm text-white/80">{{ __('app.eye_examination') }}</p>
                            </div>
                        </button>

                        <a href="{{ route('glasses.create') }}"
                            class="flex items-center p-4 bg-accent-500 rounded-xl hover:shadow-lg transition-all duration-200 group shadow-sm hover:shadow-md text-white">
                            <div
                                class="w-10 h-10 bg-white/20 rounded-lg flex items-center justify-center mr-3 group-hover:scale-110 transition-transform shadow-lg">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div>
                                <p class="font-medium text-white">{{ __('app.order_glasses') }}</p>
                                <p class="text-sm text-white/80">{{ __('app.new_glasses_order') }}</p>
                            </div>
                        </a>

                        <a href="{{ route('sales.create') }}"
                            class="flex items-center p-4 bg-primary-400 rounded-xl hover:shadow-lg transition-all duration-200 group shadow-sm hover:shadow-md text-white">
                            <div
                                class="w-10 h-10 bg-white/20 rounded-lg flex items-center justify-center mr-3 group-hover:scale-110 transition-transform shadow-lg">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z">
                                    </path>
                                </svg>
                            </div>
                            <div>
                                <p class="font-medium text-white">{{ __('app.new_sale') }}</p>
                                <p class="text-sm text-white/80">{{ __('app.record_a_sale') }}</p>
                            </div>
                        </a>

                        <a href="{{ route('expenses.create') }}"
                            class="flex items-center p-4 bg-accent-500 rounded-xl hover:shadow-lg transition-all duration-200 group shadow-sm hover:shadow-md text-white">
                            <div
                                class="w-10 h-10 bg-white/20 rounded-lg flex items-center justify-center mr-3 group-hover:scale-110 transition-transform shadow-lg">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                                    </path>
                                </svg>
                            </div>
                            <div>
                                <p class="font-medium text-white">{{ __('app.add_new_expense') }}</p>
                                <p class="text-sm text-white/80">{{ __('app.create_expense') }}</p>
                            </div>
                        </a>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="lg:col-span-2 card-secondary p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('app.recent_activity') }}</h3>
                    <div class="space-y-4">
                        <div class="flex items-center p-3 bg-gray-50 rounded-lg">
                            <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center mr-3">
                                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z">
                                    </path>
                                </svg>
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-900">{{ __('app.new_patient_registered') }}</p>
                                <p class="text-xs text-gray-500">2 {{ __('app.minutes_ago') }}</p>
                            </div>
                        </div>

                        <div class="flex items-center p-3 bg-gray-50 rounded-lg">
                            <div class="w-8 h-8 bg-emerald-100 rounded-full flex items-center justify-center mr-3">
                                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-900">{{ __('app.eye_exam_completed') }}</p>
                                <p class="text-xs text-gray-500">15 {{ __('app.minutes_ago') }}</p>
                            </div>
                        </div>

                        <div class="flex items-center p-3 bg-gray-50 rounded-lg">
                            <div class="w-8 h-8 bg-amber-100 rounded-full flex items-center justify-center mr-3">
                                <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-900">{{ __('app.glasses_order_ready') }}</p>
                                <p class="text-xs text-gray-500">1 {{ __('app.hour_ago') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Monthly Overview -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2 card-secondary p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('app.this_month_overview') }}</h3>
                    <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                        <div class="text-center p-4 bg-primary-100 rounded-xl">
                            <div class="text-2xl font-bold text-primary-600">{{ $monthStats['patients'] ?? 0 }}</div>
                            <div class="text-sm text-primary-700">{{ __('app.patients') }}</div>
                        </div>
                        <div class="text-center p-4 bg-secondary-100 rounded-xl">
                            <div class="text-2xl font-bold text-secondary-600">{{ $monthStats['exams'] ?? 0 }}</div>
                            <div class="text-sm text-secondary-700">{{ __('app.exams') }}</div>
                        </div>
                        <div class="text-center p-4 bg-accent-100 rounded-xl">
                            <div class="text-2xl font-bold text-accent-600">{{ $monthStats['glasses_delivered'] ?? 0 }}
                            </div>
                            <div class="text-sm text-accent-700">{{ __('app.glasses') }}</div>
                        </div>
                        <div class="text-center p-4 bg-primary-200 rounded-xl">
                            <div class="text-2xl font-bold text-primary-700">
                                {{ __('app.currency_symbol') }}{{ number_format($monthStats['revenue'] ?? 0, 0) }}
                            </div>
                            <div class="text-sm text-primary-800">{{ __('app.revenue') }}</div>
                        </div>
                        <div class="text-center p-4 bg-accent-100 rounded-xl">
                            <div class="text-2xl font-bold text-accent-600">
                                {{ __('app.currency_symbol') }}{{ number_format($monthStats['expense_amount'] ?? 0, 0) }}
                            </div>
                            <div class="text-sm text-accent-700">{{ __('app.expenses') }}</div>
                        </div>
                    </div>
                </div>

                <!-- Alerts -->
                <div class="card-secondary p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('app.alerts_reminders') }}</h3>
                    <div class="space-y-3">
                        <div class="flex items-start p-3 bg-red-50 rounded-lg border border-red-200">
                            <div class="w-2 h-2 bg-red-500 rounded-full mt-2 mr-3"></div>
                            <div>
                                <p class="text-sm font-medium text-red-800">{{ __('app.payment_overdue') }}</p>
                                <p class="text-xs text-red-600">3 {{ __('app.patients_outstanding_payments') }}</p>
                            </div>
                        </div>

                        <div class="flex items-start p-3 bg-yellow-50 rounded-lg border border-yellow-200">
                            <div class="w-2 h-2 bg-yellow-500 rounded-full mt-2 mr-3"></div>
                            <div>
                                <p class="text-sm font-medium text-yellow-800">{{ __('app.glasses_ready') }}</p>
                                <p class="text-xs text-yellow-600">5 {{ __('app.glasses_ready_pickup') }}</p>
                            </div>
                        </div>

                        <div class="flex items-start p-3 bg-blue-50 rounded-lg border border-blue-200">
                            <div class="w-2 h-2 bg-blue-500 rounded-full mt-2 mr-3"></div>
                            <div>
                                <p class="text-sm font-medium text-blue-800">{{ __('app.appointment') }}</p>
                                <p class="text-xs text-blue-600">Next appointment in 30 minutes</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Patient Modal -->
    <x-patient-modal />

    <!-- Exam Modal -->
    <x-exam-modal />
</x-app-layout>