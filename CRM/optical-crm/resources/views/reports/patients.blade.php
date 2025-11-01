<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('app.patient_report') }}
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('reports.export', 'patients') }}"
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
                            <div class="text-3xl font-bold text-teal-600">{{ $totalPatients }}</div>
                            <div class="text-sm text-gray-500">{{ __('app.total_patients') }}</div>
                        </div>
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg card-secondary">
                    <div class="p-6">
                        <div class="text-center">
                            <div class="text-3xl font-bold text-blue-600">{{ $totalExams }}</div>
                            <div class="text-sm text-gray-500">{{ __('app.total_exams') }}</div>
                        </div>
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg card-secondary">
                    <div class="p-6">
                        <div class="text-center">
                            <div class="text-3xl font-bold text-primary-600">{{ $totalGlasses }}</div>
                            <div class="text-sm text-gray-500">{{ __('app.glasses_orders') }}</div>
                        </div>
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg card-secondary">
                    <div class="p-6">
                        <div class="text-center">
                            <div class="text-3xl font-bold text-purple-600">{{ $totalSales }}</div>
                            <div class="text-sm text-gray-500">{{ __('app.total_sales') }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Patients Table -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg card-primary">
                <div class="p-6">
                    @if($patients->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 data-table">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            {{ __('app.patient') }}</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            {{ __('app.contact') }}</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            {{ __('app.exams') }}</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            {{ __('app.glasses') }}</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            {{ __('app.sales') }}</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            {{ __('app.member_since') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($patients as $patient)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900">{{ $patient->name }}</div>
                                                @if($patient->birth_date)
                                                    <div class="text-sm text-gray-500">{{ __('app.age') }}: {{ $patient->birth_date->age }}</div>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900">{{ $patient->phone }}</div>
                                                @if($patient->email)
                                                    <div class="text-sm text-gray-500">{{ $patient->email }}</div>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $patient->exams_count }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $patient->glasses_count }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $patient->sales_count }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $patient->created_at->format('M d, Y') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-12">
                            <p class="text-gray-500">{{ __('app.no_patient_data') }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
