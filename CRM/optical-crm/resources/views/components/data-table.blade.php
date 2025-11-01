@props([
    'headers' => [],
    'rows' => [],
    'actions' => true,
    'searchable' => true,
    'filterable' => true,
    'pagination' => null,
    'emptyMessage' => 'No data available',
    'emptyDescription' => 'There are no records to display.',
    'searchPlaceholder' => 'Search...',
    'searchValue' => '',
    'filters' => [],
    'sortable' => false,
    'sortColumn' => '',
    'sortDirection' => 'asc'
])

<div class="card-primary overflow-hidden rounded-2xl">
    @if($searchable || $filterable)
        <div class="p-6 border-b border-gray-200">
            <form method="GET" action="{{ request()->url() }}" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-{{ count($filters) + ($searchable ? 1 : 0) + 1 }} gap-4">
                    @if($searchable)
                        <div>
                            <label for="search" class="block text-sm font-medium text-gray-700">
                                {{ __('app.search') }}
                            </label>
                            <input type="text" 
                                   name="search" 
                                   id="search" 
                                   value="{{ $searchValue }}"
                                   placeholder="{{ $searchPlaceholder }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                        </div>
                    @endif

                    @foreach($filters as $filter)
                        <div>
                            <label for="{{ $filter['name'] }}" class="block text-sm font-medium text-gray-700">
                                {{ $filter['label'] }}
                            </label>
                            @if($filter['type'] === 'select')
                                <select name="{{ $filter['name'] }}" 
                                        id="{{ $filter['name'] }}"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                                    @foreach($filter['options'] as $value => $label)
                                        <option value="{{ $value }}" {{ request($filter['name']) == $value ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            @elseif($filter['type'] === 'date')
                                <input type="date" 
                                       name="{{ $filter['name'] }}" 
                                       id="{{ $filter['name'] }}" 
                                       value="{{ request($filter['name']) }}"
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                            @endif
                        </div>
                    @endforeach

                    <div class="flex items-end">
                        <button type="submit"
                                class="w-full bg-primary-500 hover:bg-primary-600 text-white px-4 py-2 rounded-xl font-medium shadow-lg hover:shadow-xl transition-all duration-300">
                            {{ __('app.filter') }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    @endif

    <div class="p-6">
        @if(count($rows) > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 data-table">
                    <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
                        <tr>
                            @foreach($headers as $header)
                                <th class="px-6 py-4 text-xs font-semibold text-gray-600 uppercase tracking-wider
                                           {{ $loop->last ? 'text-right' : 'text-left' }}">
                                    <div class="flex items-center {{ $loop->last ? 'justify-end' : '' }}
                                                [dir='rtl']_&:{{ $loop->last ? 'justify-start' : 'justify-end' }}">
                                        @if(isset($header['icon']))
                                            <svg class="w-4 h-4 text-primary-600
                                                        {{ app()->getLocale() === 'ar' ? ($loop->last ? 'mr-2' : 'ml-2') : ($loop->last ? 'ml-2' : 'mr-2') }}"
                                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                {!! $header['icon'] !!}
                                            </svg>
                                        @endif
                                        <span>{{ $header['label'] }}</span>
                                        @if($sortable && isset($header['sortable']) && $header['sortable'])
                                            <button class="{{ app()->getLocale() === 'ar' ? 'mr-2' : 'ml-2' }} text-gray-400 hover:text-gray-600">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                          d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path>
                                                </svg>
                                            </button>
                                        @endif
                                    </div>
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($rows as $row)
                            <tr class="hover:bg-gradient-to-r hover:from-primary-50 hover:to-accent-50 transition-all duration-200 border-b border-gray-100">
                                @foreach($headers as $headerKey => $header)
                                    <td class="px-6 py-4 {{ $loop->last ? 'text-right' : 'text-left' }} {{ isset($header['classes']) ? $header['classes'] : 'whitespace-nowrap' }}">
                                        @if(isset($row[$headerKey]))
                                            <div class="{{ $loop->last ? 'action-buttons flex space-x-2' : '' }}">
                                                {!! $row[$headerKey] !!}
                                            </div>
                                        @else
                                            <span class="text-gray-400">-</span>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($pagination)
                <div class="mt-6">
                    {{ $pagination }}
                </div>
            @endif
        @else
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">{{ $emptyMessage }}</h3>
                <p class="mt-1 text-sm text-gray-500">{{ $emptyDescription }}</p>
            </div>
        @endif
    </div>
</div>

<style>
    /* RTL Support for DataTable */
    [dir="rtl"] .data-table th,
    [dir="rtl"] .data-table td {
        text-align: right;
    }

    [dir="rtl"] .data-table th:first-child,
    [dir="rtl"] .data-table td:first-child {
        text-align: right;
    }

    [dir="rtl"] .data-table th:last-child,
    [dir="rtl"] .data-table td:last-child {
        text-align: left;
    }

    /* LTR Support */
    [dir="ltr"] .data-table th,
    [dir="ltr"] .data-table td {
        text-align: left;
    }

    [dir="ltr"] .data-table th:last-child,
    [dir="ltr"] .data-table td:last-child {
        text-align: right;
    }

    /* Responsive table improvements */
    @media (max-width: 768px) {
        .data-table {
            font-size: 0.875rem;
        }
        
        .data-table th,
        .data-table td {
            padding: 0.75rem 0.5rem;
        }
    }

    /* Action buttons spacing for RTL/LTR */
    [dir="rtl"] .data-table .action-buttons {
        flex-direction: row-reverse;
    }

    [dir="ltr"] .data-table .action-buttons {
        flex-direction: row;
    }
</style>
