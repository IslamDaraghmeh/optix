<x-app-layout>
    <x-slot name="header">
        <div class="rounded-2xl p-8 shadow-lg">
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="font-display text-3xl">
                        {{ __('app.edit_expense') }}
                    </h2>
                    <p class="text-gray-600 mt-2">{{ $expense->title }}</p>
                </div>
                <div>
                    <a href="{{ route('expenses.index') }}"
                        class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-3 rounded-xl font-medium transition-colors duration-200 flex items-center space-x-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        <span>{{ __('app.back_to_expenses') }}</span>
                    </a>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-2xl shadow-lg p-8">
                <form method="POST" action="{{ route('expenses.update', $expense) }}" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <!-- Title -->
                    <div>
                        <label for="title" class="block text-sm font-medium text-gray-700 mb-2">
                            {{ __('app.expense_title') }} <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="title" name="title" value="{{ old('title', $expense->title) }}"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent @error('title') border-red-500 @enderror"
                            placeholder="{{ __('app.enter_expense_title') }}" required>
                        @error('title')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Description -->
                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                            {{ __('app.description') }}
                        </label>
                        <textarea id="description" name="description" rows="3"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent @error('description') border-red-500 @enderror"
                            placeholder="{{ __('app.description') }}">{{ old('description', $expense->description) }}</textarea>
                        @error('description')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Amount and Category -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="amount" class="block text-sm font-medium text-gray-700 mb-2">
                                {{ __('app.expense_amount') }} <span class="text-red-500">*</span>
                            </label>
                            <input type="number" id="amount" name="amount" value="{{ old('amount', $expense->amount) }}"
                                step="0.01" min="0.01"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent @error('amount') border-red-500 @enderror"
                                placeholder="{{ __('app.enter_expense_amount') }}" required>
                            @error('amount')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="category" class="block text-sm font-medium text-gray-700 mb-2">
                                {{ __('app.expense_category') }} <span class="text-red-500">*</span>
                            </label>
                            <select id="category" name="category"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent @error('category') border-red-500 @enderror"
                                required>
                                <option value="">{{ __('app.select_category') }}</option>
                                @foreach(\App\Models\Expense::getCategories() as $key => $value)
                                    <option value="{{ $key }}" {{ old('category', $expense->category) == $key ? 'selected' : '' }}>
                                        {{ __('app.' . $key) }}
                                    </option>
                                @endforeach
                            </select>
                            @error('category')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Expense Date and Payment Method -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="expense_date" class="block text-sm font-medium text-gray-700 mb-2">
                                {{ __('app.expense_date') }} <span class="text-red-500">*</span>
                            </label>
                            <input type="date" id="expense_date" name="expense_date"
                                value="{{ old('expense_date', $expense->expense_date->format('Y-m-d')) }}"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent @error('expense_date') border-red-500 @enderror"
                                required>
                            @error('expense_date')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="payment_method" class="block text-sm font-medium text-gray-700 mb-2">
                                {{ __('app.payment_method') }} <span class="text-red-500">*</span>
                            </label>
                            <select id="payment_method" name="payment_method"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent @error('payment_method') border-red-500 @enderror"
                                required>
                                <option value="">{{ __('app.select_payment_method') }}</option>
                                @foreach(\App\Models\Expense::getPaymentMethods() as $key => $value)
                                    <option value="{{ $key }}" {{ old('payment_method', $expense->payment_method) == $key ? 'selected' : '' }}>
                                        {{ __('app.' . $key) }}
                                    </option>
                                @endforeach
                            </select>
                            @error('payment_method')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Receipt Number and Vendor -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="receipt_number" class="block text-sm font-medium text-gray-700 mb-2">
                                {{ __('app.receipt_number') }}
                            </label>
                            <input type="text" id="receipt_number" name="receipt_number"
                                value="{{ old('receipt_number', $expense->receipt_number) }}"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent @error('receipt_number') border-red-500 @enderror"
                                placeholder="{{ __('app.enter_receipt_number') }}">
                            @error('receipt_number')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="vendor" class="block text-sm font-medium text-gray-700 mb-2">
                                {{ __('app.vendor') }}
                            </label>
                            <input type="text" id="vendor" name="vendor" value="{{ old('vendor', $expense->vendor) }}"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent @error('vendor') border-red-500 @enderror"
                                placeholder="{{ __('app.enter_vendor') }}">
                            @error('vendor')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Notes -->
                    <div>
                        <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
                            {{ __('app.notes') }}
                        </label>
                        <textarea id="notes" name="notes" rows="3"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent @error('notes') border-red-500 @enderror"
                            placeholder="{{ __('app.enter_notes') }}">{{ old('notes', $expense->notes) }}</textarea>
                        @error('notes')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Submit Button -->
                    <div class="flex justify-end space-x-4">
                        <a href="{{ route('expenses.index') }}"
                            class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors duration-200">
                            {{ __('app.cancel') }}
                        </a>
                        <button type="submit"
                            class="px-6 py-3 bg-primary-500 hover:bg-primary-600 text-white rounded-lg font-medium transition-colors duration-200">
                            {{ __('app.update_expense') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
