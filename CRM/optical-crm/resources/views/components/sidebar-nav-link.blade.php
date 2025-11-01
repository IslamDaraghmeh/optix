@props(['active'])

@php
    $classes = ($active ?? false)
        ? 'bg-primary-50 border-r-4 border-primary-500 text-primary-700 font-medium'
        : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900';
@endphp

<a {{ $attributes->merge(['class' => 'flex items-center px-4 py-3 text-sm rounded-lg transition-all duration-200 ' . $classes]) }}>
    {{ $slot }}
</a>