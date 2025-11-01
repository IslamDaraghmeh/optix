<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center animate-fadeInUp">
            <div class="flex items-center space-x-4">
                <div class="bg-white/20 backdrop-blur-sm p-3 rounded-xl border border-white/30 shadow-lg">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
                <div>
                    <h2 class="font-display text-3xl text-white leading-tight drop-shadow-lg">
                        {{ __('app.patients') }}
                    </h2>
                    <p class="text-white/90 text-sm mt-1 font-medium">{{ __('Manage patient records and information') }}</p>
                </div>
            </div>
            <button onclick="openModal('patientModal')"
                class="bg-white hover:bg-white/95 text-primary-800 hover:text-primary-900 px-6 py-3 rounded-xl font-semibold shadow-xl hover:shadow-2xl transition-all duration-300 transform hover:scale-105 border-2 border-white/80 backdrop-blur-sm">
                <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                {{ __('app.add_new_patient') }}
            </button>
        </div>
    </x-slot>

    <div class="py-8 animate-fadeInUp" style="animation-delay: 0.1s">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl p-6 text-white shadow-xl hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-1">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-white/95 text-sm font-semibold uppercase tracking-wide">{{ __('Total Patients') }}</p>
                            <p class="text-4xl font-bold mt-2 text-white">{{ \App\Models\Patient::count() }}</p>
                        </div>
                        <div class="bg-white/25 p-3 rounded-xl backdrop-blur-sm">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
                            </svg>
                        </div>
                    </div>
                </div>
                <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-2xl p-6 text-white shadow-xl hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-1">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-white/95 text-sm font-semibold uppercase tracking-wide">{{ __('New This Month') }}</p>
                            <p class="text-4xl font-bold mt-2 text-white">{{ \App\Models\Patient::whereMonth('created_at', now()->month)->count() }}</p>
                        </div>
                        <div class="bg-white/25 p-3 rounded-xl backdrop-blur-sm">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                            </svg>
                        </div>
                    </div>
                </div>
                <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-2xl p-6 text-white shadow-xl hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-1">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-white/95 text-sm font-semibold uppercase tracking-wide">{{ __('Total Exams') }}</p>
                            <p class="text-4xl font-bold mt-2 text-white">{{ \App\Models\Exam::count() }}</p>
                        </div>
                        <div class="bg-white/25 p-3 rounded-xl backdrop-blur-sm">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                    </div>
                </div>
                <div class="bg-gradient-to-br from-orange-500 to-orange-600 rounded-2xl p-6 text-white shadow-xl hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-1">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-white/95 text-sm font-semibold uppercase tracking-wide">{{ __('Glasses Orders') }}</p>
                            <p class="text-4xl font-bold mt-2 text-white">{{ \App\Models\Glass::count() }}</p>
                        </div>
                        <div class="bg-white/25 p-3 rounded-xl backdrop-blur-sm">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- DataTable Card -->
            <div class="card-primary overflow-hidden rounded-2xl shadow-2xl border-2 border-primary-100">
                <div class="bg-gradient-to-r from-primary-50 to-secondary-50 px-6 py-4 border-b-2 border-primary-200">
                    <h3 class="font-display text-xl text-primary-900 font-bold flex items-center">
                        <svg class="w-6 h-6 mr-2 text-primary-700" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        {{ __('Patients Directory') }}
                    </h3>
                </div>
                <div class="p-6 bg-white">
                    <div class="overflow-x-auto">
                        <table id="patientsTable" class="min-w-full divide-y divide-gray-200 display nowrap" style="width:100%">
                            <thead class="bg-gradient-to-r from-primary-600 to-primary-700">
                                <tr>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-white uppercase tracking-wider">{{ __('app.name') }}</th>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-white uppercase tracking-wider">{{ __('app.phone') }}</th>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-white uppercase tracking-wider">{{ __('app.email') }}</th>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-white uppercase tracking-wider">{{ __('app.birth_date') }}</th>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-white uppercase tracking-wider">{{ __('app.address') }}</th>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-white uppercase tracking-wider">{{ __('Stats') }}</th>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-white uppercase tracking-wider">{{ __('app.created') }}</th>
                                    <th class="px-6 py-4 text-right text-xs font-bold text-white uppercase tracking-wider">{{ __('app.actions') }}</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Patient Modal -->
    <x-patient-modal />

    @push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css">
    <style>
        #patientsTable_wrapper .dt-buttons {
            margin-bottom: 1rem;
            float: left;
        }
        #patientsTable_wrapper .dt-button {
            background: linear-gradient(135deg, #17877B 0%, #14A38B 100%);
            color: white !important;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 0.75rem;
            font-weight: 500;
            margin-right: 0.5rem;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        #patientsTable_wrapper .dt-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        }
        #patientsTable_wrapper .dataTables_filter {
            float: right;
        }
        #patientsTable_wrapper .dataTables_filter input {
            border: 1px solid #d1d5db;
            border-radius: 0.5rem;
            padding: 0.5rem 1rem;
            margin-left: 0.5rem;
        }
        #patientsTable_wrapper .dataTables_length select {
            border: 1px solid #d1d5db;
            border-radius: 0.5rem;
            padding: 0.25rem 0.5rem;
            margin: 0 0.5rem;
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button {
            padding: 0.25rem 0.75rem;
            margin: 0 0.125rem;
            border-radius: 0.375rem;
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            background: linear-gradient(135deg, #17877B 0%, #14A38B 100%);
            color: white !important;
            border: none;
        }
    </style>
    @endpush

    @push('scripts')
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>

    <script>
    $(document).ready(function() {
        // Setup CSRF token for all AJAX requests
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $('#patientsTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: '{{ route('patients.index') }}',
            columns: [
                { data: 'name', name: 'name' },
                { data: 'phone', name: 'phone' },
                { data: 'email', name: 'email' },
                { data: 'birth_date', name: 'birth_date' },
                { data: 'address', name: 'address' },
                { data: 'stats', name: 'stats', orderable: false, searchable: false },
                { data: 'created_at', name: 'created_at' },
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ],
            dom: 'Bfrtip',
            buttons: [
                {
                    extend: 'copy',
                    text: '<i class="fas fa-copy"></i> Copy',
                    exportOptions: { columns: ':visible:not(:last-child)' }
                },
                {
                    extend: 'csv',
                    text: '<i class="fas fa-file-csv"></i> CSV',
                    exportOptions: { columns: ':visible:not(:last-child)' }
                },
                {
                    extend: 'excel',
                    text: '<i class="fas fa-file-excel"></i> Excel',
                    exportOptions: { columns: ':visible:not(:last-child)' }
                },
                {
                    extend: 'pdf',
                    text: '<i class="fas fa-file-pdf"></i> PDF',
                    exportOptions: { columns: ':visible:not(:last-child)' },
                    orientation: 'landscape',
                    pageSize: 'A4'
                },
                {
                    extend: 'print',
                    text: '<i class="fas fa-print"></i> Print',
                    exportOptions: { columns: ':visible:not(:last-child)' }
                }
            ],
            responsive: true,
            pageLength: 25,
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "{{ __('All') }}"]],
            order: [[6, 'desc']], // Order by created_at descending
            language: {
                processing: '<div class="flex items-center justify-center"><svg class="animate-spin h-8 w-8 text-primary-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg><span class="ml-2">{{ __('Loading...') }}</span></div>',
                search: "_INPUT_",
                searchPlaceholder: "{{ __('Search patients...') }}",
                lengthMenu: "{{ __('Show') }} _MENU_ {{ __('entries') }}",
                info: "{{ __('Showing') }} _START_ {{ __('to') }} _END_ {{ __('of') }} _TOTAL_ {{ __('entries') }}",
                infoEmpty: "{{ __('No records available') }}",
                infoFiltered: "({{ __('filtered from') }} _MAX_ {{ __('total entries') }})",
                zeroRecords: "{{ __('No matching records found') }}",
                emptyTable: "{{ __('No data available in table') }}",
                paginate: {
                    first: "{{ __('First') }}",
                    last: "{{ __('Last') }}",
                    next: "{{ __('Next') }}",
                    previous: "{{ __('Previous') }}"
                }
            }
        });
    });
    </script>
    @endpush
</x-app-layout>
