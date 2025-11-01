<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('app.glasses_report') }}
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('reports.export', 'glasses') }}"
                    class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition-colors">
                    {{ __('app.export_pdf') }}
                </a>
                <a href="{{ route('reports.index') }}"
                    class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-colors">
                    {{ __('app.back_to_reports') }}
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Summary Statistics -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg card-secondary">
                    <div class="p-6">
                        <div class="text-center">
                            <div class="text-3xl font-bold text-primary-600">{{ $totalGlasses }}</div>
                            <div class="text-sm text-gray-500">{{ __('app.total_orders') }}</div>
                        </div>
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg card-secondary">
                    <div class="p-6">
                        <div class="text-center">
                            <div class="text-3xl font-bold text-blue-600">${{ number_format($totalValue, 2) }}</div>
                            <div class="text-sm text-gray-500">{{ __('app.total_value') }}</div>
                        </div>
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg card-secondary">
                    <div class="p-6">
                        <div class="text-center">
                            <div class="text-3xl font-bold text-yellow-600">{{ $statusCounts['pending'] ?? 0 }}</div>
                            <div class="text-sm text-gray-500">{{ __('app.pending') }}</div>
                        </div>
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg card-secondary">
                    <div class="p-6">
                        <div class="text-center">
                            <div class="text-3xl font-bold text-primary-600">{{ $statusCounts['delivered'] ?? 0 }}</div>
                            <div class="text-sm text-gray-500">{{ __('app.delivered') }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Glasses Table -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg card-primary">
                <div class="p-6">
                    @if($glasses->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 data-table">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            {{ __('app.patient') }}</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            {{ __('app.lens_type') }}</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            {{ __('app.frame_type') }}</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            {{ __('app.price') }}</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            {{ __('app.status') }}</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            {{ __('app.order_date') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($glasses as $glass)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900">{{ $glass->patient->name }}</div>
                                                <div class="text-sm text-gray-500">{{ $glass->patient->phone }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $glass->lens_type }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $glass->frame_type }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                ${{ number_format($glass->price, 2) }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span
                                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $glass->status_badge }}">
                                                    {{ ucfirst($glass->status) }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $glass->created_at->format('M d, Y') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-12">
                            <p class="text-gray-500">{{ __('app.no_glasses_data') }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
