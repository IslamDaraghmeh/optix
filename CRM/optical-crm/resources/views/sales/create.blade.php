<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('app.create_new_sale') }}
            </h2>
            <a href="{{ route('sales.index') }}"
                class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-colors">
                {{ __('app.back_to_sales') }}
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ route('sales.store') }}" id="saleForm" class="space-y-6">
                        @csrf

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Patient Selection -->
                            <div class="md:col-span-2">
                                <label for="patient_id"
                                    class="block text-sm font-medium text-gray-700">{{ __('app.patient') }}</label>
                                <select name="patient_id" id="patient_id"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 @error('patient_id') border-red-300 @enderror">
                                    <option value="">{{ __('app.walk_in_customer_no_patient') }}</option>
                                    @foreach($patients as $patient)
                                        <option value="{{ $patient->id }}" {{ old('patient_id', $selectedPatientId) == $patient->id ? 'selected' : '' }}>
                                            {{ $patient->name }} - {{ $patient->phone }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('patient_id')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Sale Date -->
                            <div>
                                <label for="sale_date"
                                    class="block text-sm font-medium text-gray-700">{{ __('app.sale_date') }}
                                    *</label>
                                <input type="date" name="sale_date" id="sale_date"
                                    value="{{ old('sale_date', date('Y-m-d')) }}" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 @error('sale_date') border-red-300 @enderror">
                                @error('sale_date')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Items Section -->
                        <div class="border-t pt-6">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-lg font-medium text-gray-900">{{ __('app.sale_items') }}</h3>
                                <button type="button" id="addItem"
                                    class="bg-primary-500 hover:bg-primary-600 text-white px-4 py-2 rounded-lg transition-colors">
                                    {{ __('app.add_item') }}
                                </button>
                            </div>

                            <div id="itemsContainer" class="space-y-4">
                                <div class="item-row border rounded-lg p-4 bg-gray-50">
                                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                        <div>
                                            <label
                                                class="block text-sm font-medium text-gray-700">{{ __('app.item_name') }}
                                                *</label>
                                            <input type="text" name="items[0][name]" required
                                                placeholder="{{ __('app.item_name_placeholder') }}"
                                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                                        </div>
                                        <div>
                                            <label
                                                class="block text-sm font-medium text-gray-700">{{ __('app.quantity') }}
                                                *</label>
                                            <input type="number" name="items[0][quantity]" min="1" value="1" required
                                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 item-quantity">
                                        </div>
                                        <div>
                                            <label
                                                class="block text-sm font-medium text-gray-700">{{ __('app.unit_price') }}
                                                *</label>
                                            <input type="number" step="0.01" name="items[0][price]" min="0" required
                                                placeholder="0.00"
                                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 item-price">
                                        </div>
                                        <div class="flex items-end">
                                            <button type="button"
                                                class="remove-item bg-red-500 hover:bg-red-600 text-white px-3 py-2 rounded-lg transition-colors hidden">
                                                {{ __('app.remove') }}
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Payment Section -->
                        <div class="border-t pt-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('app.payment_information') }}</h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div>
                                    <label for="total_price"
                                        class="block text-sm font-medium text-gray-700">{{ __('app.total_price') }}</label>
                                    <input type="number" step="0.01" name="total_price" id="total_price" readonly
                                        class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm">
                                </div>
                                <div>
                                    <label for="paid_amount"
                                        class="block text-sm font-medium text-gray-700">{{ __('app.paid_amount') }}
                                        *</label>
                                    <input type="number" step="0.01" name="paid_amount" id="paid_amount"
                                        value="{{ old('paid_amount', 0) }}" min="0" required
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 @error('paid_amount') border-red-300 @enderror">
                                    @error('paid_amount')
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label for="remaining_amount"
                                        class="block text-sm font-medium text-gray-700">{{ __('app.remaining_amount') }}</label>
                                    <input type="number" step="0.01" name="remaining_amount" id="remaining_amount"
                                        readonly
                                        class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm">
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="flex justify-end space-x-4 pt-6 border-t">
                            <a href="{{ route('sales.index') }}"
                                class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-6 py-2 rounded-lg transition-colors">
                                {{ __('app.cancel') }}
                            </a>
                            <button type="submit"
                                class="bg-primary-500 hover:bg-primary-600 text-white px-6 py-2 rounded-lg transition-colors">
                                {{ __('app.create_sale') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        let itemIndex = 1;

        // Add item functionality
        document.getElementById('addItem').addEventListener('click', function () {
            const container = document.getElementById('itemsContainer');
            const newItem = document.querySelector('.item-row').cloneNode(true);

            // Update input names and clear values
            newItem.querySelectorAll('input').forEach(input => {
                const name = input.name;
                input.name = name.replace(/\[\d+\]/, `[${itemIndex}]`);
                input.value = input.type === 'number' && input.classList.contains('item-quantity') ? '1' : '';
            });

            // Show remove button
            newItem.querySelector('.remove-item').classList.remove('hidden');

            container.appendChild(newItem);
            itemIndex++;
        });

        // Remove item functionality
        document.addEventListener('click', function (e) {
            if (e.target.classList.contains('remove-item')) {
                const itemRows = document.querySelectorAll('.item-row');
                if (itemRows.length > 1) {
                    e.target.closest('.item-row').remove();
                    calculateTotal();
                }
            }
        });

        // Calculate total
        function calculateTotal() {
            let total = 0;
            document.querySelectorAll('.item-row').forEach(row => {
                const quantity = parseFloat(row.querySelector('.item-quantity').value) || 0;
                const price = parseFloat(row.querySelector('.item-price').value) || 0;
                total += quantity * price;
            });

            document.getElementById('total_price').value = total.toFixed(2);
            calculateRemaining();
        }

        // Calculate remaining amount
        function calculateRemaining() {
            const total = parseFloat(document.getElementById('total_price').value) || 0;
            const paid = parseFloat(document.getElementById('paid_amount').value) || 0;
            const remaining = Math.max(0, total - paid);

            document.getElementById('remaining_amount').value = remaining.toFixed(2);
        }

        // Event listeners
        document.addEventListener('input', function (e) {
            if (e.target.classList.contains('item-quantity') || e.target.classList.contains('item-price')) {
                calculateTotal();
            } else if (e.target.id === 'paid_amount') {
                calculateRemaining();
            }
        });

        // Initial calculation
        calculateTotal();
    </script>
</x-app-layout>