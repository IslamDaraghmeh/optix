<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center animate-fadeInUp">
            <div class="flex items-center space-x-4">
                <div class="bg-white/20 backdrop-blur-sm p-3 rounded-xl border border-white/30 shadow-lg">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                </div>
                <div>
                    <h2 class="font-display text-3xl text-white leading-tight drop-shadow-lg">
                        {{ __('app.stock') }}
                    </h2>
                    <p class="text-white/90 text-sm mt-1 font-medium">{{ __('Monitor inventory and stock levels') }}</p>
                </div>
            </div>
            <a href="{{ route('stock.create') }}"
                class="bg-white hover:bg-white/95 text-primary-800 hover:text-primary-900 px-6 py-3 rounded-xl font-semibold shadow-xl hover:shadow-2xl transition-all duration-300 transform hover:scale-105 border-2 border-white/80 backdrop-blur-sm">
                <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                {{ __('Add New Stock Item') }}
            </a>
        </div>
    </x-slot>

    <div class="py-8 animate-fadeInUp" style="animation-delay: 0.1s">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl p-6 text-white shadow-xl hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-1">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-white/95 text-sm font-semibold uppercase tracking-wide">{{ __('Total Items') }}</p>
                            <p class="text-4xl font-bold mt-2 text-white">{{ \App\Models\Stock::count() }}</p>
                        </div>
                        <div class="bg-white/25 p-3 rounded-xl backdrop-blur-sm">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                            </svg>
                        </div>
                    </div>
                </div>
                <div class="bg-gradient-to-br from-yellow-500 to-yellow-600 rounded-2xl p-6 text-white shadow-xl hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-1">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-white/95 text-sm font-semibold uppercase tracking-wide">{{ __('Low Stock Items') }}</p>
                            <p class="text-4xl font-bold mt-2 text-white">{{ \App\Models\Stock::where('quantity', '<=', 10)->count() }}</p>
                        </div>
                        <div class="bg-white/25 p-3 rounded-xl backdrop-blur-sm">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                    </div>
                </div>
                <div class="bg-gradient-to-br from-red-500 to-red-600 rounded-2xl p-6 text-white shadow-xl hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-1">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-white/95 text-sm font-semibold uppercase tracking-wide">{{ __('Out of Stock') }}</p>
                            <p class="text-4xl font-bold mt-2 text-white">{{ \App\Models\Stock::where('quantity', '<=', 0)->count() }}</p>
                        </div>
                        <div class="bg-white/25 p-3 rounded-xl backdrop-blur-sm">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </div>
                    </div>
                </div>
                <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-2xl p-6 text-white shadow-xl hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-1">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-white/95 text-sm font-semibold uppercase tracking-wide">{{ __('Total Value') }}</p>
                            <p class="text-4xl font-bold mt-2 text-white">{{ number_format(\App\Models\Stock::sum(\DB::raw('quantity * unit_price')), 0) }}</p>
                        </div>
                        <div class="bg-white/25 p-3 rounded-xl backdrop-blur-sm">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
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
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                        {{ __('Stock Inventory') }}
                    </h3>
                </div>
                <div class="p-6 bg-white">
                    <div class="overflow-x-auto">
                        <table id="stockTable" class="min-w-full divide-y divide-gray-200 display nowrap" style="width:100%">
                            <thead class="bg-gradient-to-r from-primary-600 to-primary-700">
                                <tr>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-white uppercase tracking-wider">{{ __('app.item_name_code') }}</th>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-white uppercase tracking-wider">{{ __('app.type') }}</th>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-white uppercase tracking-wider">{{ __('app.status') }}</th>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-white uppercase tracking-wider">{{ __('app.quantity') }}</th>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-white uppercase tracking-wider">{{ __('app.prices') }}</th>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-white uppercase tracking-wider">{{ __('app.brand_supplier') }}</th>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-white uppercase tracking-wider">{{ __('app.movements') }}</th>
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

    @push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css">
    <style>
        #stockTable_wrapper .dt-buttons {
            margin-bottom: 1rem;
            float: left;
        }
        #stockTable_wrapper .dt-button {
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
        #stockTable_wrapper .dt-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        }
        #stockTable_wrapper .dataTables_filter {
            float: right;
        }
        #stockTable_wrapper .dataTables_filter input {
            border: 1px solid #d1d5db;
            border-radius: 0.5rem;
            padding: 0.5rem 1rem;
            margin-left: 0.5rem;
        }
        #stockTable_wrapper .dataTables_length select {
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

        $('#stockTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: '{{ route('stock.index') }}',
            columns: [
                { data: 'item_name_code', name: 'item_name_code' },
                { data: 'type', name: 'type' },
                { data: 'status', name: 'status' },
                { data: 'quantity', name: 'quantity' },
                { data: 'prices', name: 'prices', orderable: false, searchable: false },
                { data: 'brand_supplier', name: 'brand_supplier' },
                { data: 'movements', name: 'movements', orderable: false, searchable: false },
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
            order: [[7, 'desc']], // Order by created_at descending
            language: {
                processing: '<div class="flex items-center justify-center"><svg class="animate-spin h-8 w-8 text-primary-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg><span class="ml-2">{{ __('Loading...') }}</span></div>',
                search: "_INPUT_",
                searchPlaceholder: "{{ __('Search stock...') }}",
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
