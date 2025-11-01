<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('app.sale_details') }} #{{ str_pad($sale->id, 6, '0', STR_PAD_LEFT) }}
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('sales.edit', $sale) }}"
                    class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition-colors">
                    {{ __('app.edit_sale') }}
                </a>
                <a href="{{ route('sales.index') }}"
                    class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-colors">
                    {{ __('app.back_to_sales') }}
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <!-- Sale Information -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('app.sale_information') }}</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">{{ __('app.sale_id') }}</dt>
                            <dd class="mt-1 text-sm text-gray-900">#{{ str_pad($sale->id, 6, '0', STR_PAD_LEFT) }}</dd>
                        </div>
                        @if($sale->patient)
                            <div>
                                <dt class="text-sm font-medium text-gray-500">{{ __('app.patient_name') }}</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $sale->patient->name }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">{{ __('app.patient_phone') }}</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $sale->patient->phone }}</dd>
                            </div>
                        @else
                            <div class="md:col-span-2">
                                <dt class="text-sm font-medium text-gray-500">{{ __('app.customer_type') }}</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ __('app.walk_in_customer') }}</dd>
                            </div>
                        @endif
                        <div>
                            <dt class="text-sm font-medium text-gray-500">{{ __('app.sale_date') }}</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $sale->sale_date->format('M d, Y') }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">{{ __('app.total_price') }}</dt>
                            <dd class="mt-1 text-sm text-gray-900 font-semibold">
                                ${{ number_format($sale->total_price, 2) }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">{{ __('app.paid_amount') }}</dt>
                            <dd class="mt-1 text-sm text-gray-900 font-semibold">
                                ${{ number_format($sale->paid_amount, 2) }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">{{ __('app.remaining_amount') }}</dt>
                            <dd class="mt-1 text-sm text-gray-900 font-semibold">
                                ${{ number_format($sale->remaining_amount, 2) }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">{{ __('app.payment_status') }}</dt>
                            <dd class="mt-1">
                                @if($sale->is_paid)
                                    <span
                                        class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-primary-800">
                                        {{ __('app.paid') }}
                                    </span>
                                @elseif($sale->paid_amount > 0)
                                    <span
                                        class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
                                        {{ __('app.partial') }}
                                    </span>
                                @else
                                    <span
                                        class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
                                        {{ __('app.unpaid') }}
                                    </span>
                                @endif
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">{{ __('app.created') }}</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $sale->created_at->format('M d, Y g:i A') }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">{{ __('app.last_updated') }}</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $sale->updated_at->format('M d, Y g:i A') }}</dd>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sale Items -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('app.sale_items') }}</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('app.item') }}
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('app.quantity') }}
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('app.unit_price') }}
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('app.total') }}
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($sale->items as $item)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $item['name'] }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $item['quantity'] }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            ${{ number_format($item['price'], 2) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-semibold">
                                            ${{ number_format($item['quantity'] * $item['price'], 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-gray-50">
                                <tr>
                                    <td colspan="3" class="px-6 py-4 text-right text-sm font-medium text-gray-900">
                                        {{ __('app.total') }}:
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">
                                        ${{ number_format($sale->total_price, 2) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Payment Update -->
            @if(!$sale->is_paid)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('app.update_payment') }}</h3>
                        <form method="POST" action="{{ route('sales.update', $sale) }}" class="flex items-end space-x-4">
                            @csrf
                            @method('PUT')

                            <div class="flex-1">
                                <label for="additional_payment"
                                    class="block text-sm font-medium text-gray-700">{{ __('app.additional_payment') }}</label>
                                <input type="number" step="0.01" name="additional_payment" id="additional_payment" min="0"
                                    max="{{ $sale->remaining_amount }}"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                            </div>

                            <button type="submit"
                                class="bg-primary-500 hover:bg-primary-600 text-white px-4 py-2 rounded-lg transition-colors">
                                {{ __('app.update_payment') }}
                            </button>
                        </form>
                        <p class="mt-2 text-sm text-gray-500">{{ __('app.maximum_payment') }}:
                            ${{ number_format($sale->remaining_amount, 2) }}</p>
                    </div>
                </div>
            @endif

            <!-- Patient Information -->
            @if($sale->patient)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('app.patient_information') }}</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">{{ __('app.full_name') }}</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $sale->patient->name }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">{{ __('app.phone_number') }}</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $sale->patient->phone }}</dd>
                            </div>
                            @if($sale->patient->email)
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">{{ __('app.email_address') }}</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $sale->patient->email }}</dd>
                                </div>
                            @endif
                            @if($sale->patient->birth_date)
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">{{ __('app.birth_date') }}</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $sale->patient->birth_date->format('M d, Y') }}
                                    </dd>
                                </div>
                            @endif
                            @if($sale->patient->address)
                                <div class="md:col-span-2">
                                    <dt class="text-sm font-medium text-gray-500">{{ __('app.address') }}</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $sale->patient->address }}</dd>
                                </div>
                            @endif
                        </div>

                        <div class="mt-6">
                            <a href="{{ route('patients.show', $sale->patient) }}"
                                class="bg-teal-500 hover:bg-teal-600 text-white px-4 py-2 rounded-lg transition-colors">
                                {{ __('app.view_patient_profile') }}
                            </a>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>