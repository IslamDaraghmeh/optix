<x-app-layout>
    <x-slot name="header">
        <div class="rounded-2xl p-8 shadow-lg">
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="font-display text-3xl">
                        {{ __('app.expense_details') }}
                    </h2>
                    <p class="text-gray-600 mt-2">{{ $expense->title }}</p>
                </div>
                <div class="flex space-x-4">
                    <a href="{{ route('expenses.index') }}"
                        class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-3 rounded-xl font-medium transition-colors duration-200 flex items-center space-x-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        <span>{{ __('app.back_to_expenses') }}</span>
                    </a>
                    <a href="{{ route('expenses.edit', $expense) }}"
                        class="bg-primary-500 hover:bg-primary-600 text-white px-6 py-3 rounded-xl font-medium transition-colors duration-200 flex items-center space-x-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                            </path>
                        </svg>
                        <span>{{ __('app.edit_expense') }}</span>
                    </a>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Main Content -->
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-2xl shadow-lg p-8">
                        <div class="space-y-6">
                            <!-- Title and Amount -->
                            <div class="border-b border-gray-200 pb-6">
                                <h3 class="text-2xl font-bold text-gray-900 mb-2">{{ $expense->title }}</h3>
                                <div class="flex items-center space-x-4">
                                    <span
                                        class="text-3xl font-bold text-primary-600">{{ __('app.currency_symbol') }}{{ number_format($expense->amount, 2) }}</span>
                                    <span
                                        class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-primary-100 text-primary-800">
                                        {{ __('app.' . $expense->category) }}
                                    </span>
                                </div>
                            </div>

                            <!-- Description -->
                            @if($expense->description)
                                <div>
                                    <h4 class="text-lg font-semibold text-gray-900 mb-3">{{ __('app.description') }}</h4>
                                    <p class="text-gray-700 leading-relaxed">{{ $expense->description }}</p>
                                </div>
                            @endif

                            <!-- Notes -->
                            @if($expense->notes)
                                <div>
                                    <h4 class="text-lg font-semibold text-gray-900 mb-3">{{ __('app.notes') }}</h4>
                                    <p class="text-gray-700 leading-relaxed">{{ $expense->notes }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Expense Details Card -->
                    <div class="bg-white rounded-2xl shadow-lg p-6">
                        <h4 class="text-lg font-semibold text-gray-900 mb-4">{{ __('app.expense_details') }}</h4>
                        <div class="space-y-4">
                            <div class="flex justify-between items-center">
                                <span class="text-sm font-medium text-gray-500">{{ __('app.expense_date') }}</span>
                                <span
                                    class="text-sm text-gray-900">{{ $expense->expense_date->format('M d, Y') }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm font-medium text-gray-500">{{ __('app.payment_method') }}</span>
                                <span class="text-sm text-gray-900">{{ __('app.' . $expense->payment_method) }}</span>
                            </div>
                            @if($expense->receipt_number)
                                <div class="flex justify-between items-center">
                                    <span class="text-sm font-medium text-gray-500">{{ __('app.receipt_number') }}</span>
                                    <span class="text-sm text-gray-900">{{ $expense->receipt_number }}</span>
                                </div>
                            @endif
                            @if($expense->vendor)
                                <div class="flex justify-between items-center">
                                    <span class="text-sm font-medium text-gray-500">{{ __('app.vendor') }}</span>
                                    <span class="text-sm text-gray-900">{{ $expense->vendor }}</span>
                                </div>
                            @endif
                            <div class="flex justify-between items-center">
                                <span class="text-sm font-medium text-gray-500">{{ __('app.created_at') }}</span>
                                <span
                                    class="text-sm text-gray-900">{{ $expense->created_at->format('M d, Y H:i') }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm font-medium text-gray-500">{{ __('app.updated_at') }}</span>
                                <span
                                    class="text-sm text-gray-900">{{ $expense->updated_at->format('M d, Y H:i') }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Actions Card -->
                    <div class="bg-white rounded-2xl shadow-lg p-6">
                        <h4 class="text-lg font-semibold text-gray-900 mb-4">{{ __('app.actions') }}</h4>
                        <div class="space-y-3">
                            <a href="{{ route('expenses.edit', $expense) }}"
                                class="w-full bg-primary-500 hover:bg-primary-600 text-white px-4 py-2 rounded-lg font-medium transition-colors duration-200 flex items-center justify-center space-x-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                    </path>
                                </svg>
                                <span>{{ __('app.edit_expense') }}</span>
                            </a>
                            <form method="POST" action="{{ route('expenses.destroy', $expense) }}" class="w-full"
                                onsubmit="return confirm('{{ __('app.confirm_delete') }}')">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    class="w-full bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg font-medium transition-colors duration-200 flex items-center justify-center space-x-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                        </path>
                                    </svg>
                                    <span>{{ __('app.delete_expense') }}</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>