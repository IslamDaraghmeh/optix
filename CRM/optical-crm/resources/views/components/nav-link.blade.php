@props(['active'])

@php
$classes = ($active ?? false)
            ? 'bg-teal-100 text-teal-700 border-teal-300 shadow-sm'
            : 'text-gray-600 hover:text-teal-600 hover:bg-teal-50 border-transparent';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
