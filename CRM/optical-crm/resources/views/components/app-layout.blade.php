@php
    use App\Services\LayoutService;
    $layout = LayoutService::getLayout();
@endphp

@if($layout === 'sidebar')
    @include('layouts.app-sidebar', ['slot' => $slot])
@else
    @include('layouts.app-navbar', ['slot' => $slot])
@endif