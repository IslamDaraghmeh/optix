<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Optical CRM') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700,800&display=swap" rel="stylesheet" />
    <link href="https://fonts.bunny.net/css?family=poppins:300,400,500,600,700,800&display=swap" rel="stylesheet" />
    <link href="https://fonts.bunny.net/css?family=cairo:300,400,500,600,700,800&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Custom Styles -->
    <style>
        :root {
            /* Primary Colors - Teal/Green Palette */
            --color-primary-50: #F5F9F9;
            /* G 4 - Almost pure white */
            --color-primary-100: #F0F5F6;
            /* G 6 - Almost pure white with subtle grey */
            --color-primary-200: #EBF2F3;
            /* G 8 - Extremely pale off-white */
            --color-primary-300: #E5EEEF;
            /* G 10 - Extremely pale off-white with subtle blue/grey */
            --color-primary-400: #D5E4E6;
            /* G 16 - Very pale light blue-grey */
            --color-primary-500: #99BDC1;
            /* G 40 - Light muted blue-grey */
            --color-primary-600: #70A2A7;
            /* G 56 - Lighter muted blue-green */
            --color-primary-700: #47878E;
            /* G 72 - Medium muted teal-blue */
            --color-primary-800: #1F6C75;
            /* G 88 - Dark muted teal-blue */
            --color-primary-900: #015D67;
            /* Forest Green - Dark rich teal-green */
            --color-primary-950: #00ACB1;
            /* Kelly Green - Vibrant bright teal */

            /* Secondary Colors - Neutral Palette */
            --color-secondary-50: #F5F9F9;
            /* G 4 - Almost pure white */
            --color-secondary-100: #F0F5F6;
            /* G 6 - Almost pure white with subtle grey */
            --color-secondary-200: #EBF2F3;
            /* G 8 - Extremely pale off-white */
            --color-secondary-300: #E5EEEF;
            /* G 10 - Extremely pale off-white */
            --color-secondary-400: #D5E4E6;
            /* G 16 - Very pale light blue-grey */
            --color-secondary-500: #ADC9CD;
            /* G 32 - Very light pale blue-grey */
            --color-secondary-600: #99BDC1;
            /* G 40 - Light muted blue-grey */
            --color-secondary-700: #70A2A7;
            /* G 56 - Lighter muted blue-green */
            --color-secondary-800: #47878E;
            /* G 72 - Medium muted teal-blue */
            --color-secondary-900: #1F6C75;
            /* G 88 - Dark muted teal-blue */
            --color-secondary-950: #015D67;
            /* Forest Green - Dark rich teal-green */

            /* Accent Colors - Mint/Teal Highlights */
            --color-accent-500: #87E4DB;
            /* Mint - Very light pastel mint green */
            --color-accent-600: #70A2A7;
            /* G 56 - Lighter muted blue-green */
            --color-accent-700: #47878E;
            /* G 72 - Medium muted teal-blue */

            /* Success, Warning, Error */
            --color-success-500: #22c55e;
            --color-warning-500: #f59e0b;
            --color-error-500: #ef4444;
        }

        body {
            font-family: 'Inter', 'Cairo', system-ui, sans-serif;
            scroll-behavior: smooth;
            background: #ffffff;
            min-height: 100vh;
            font-weight: 400;
            line-height: 1.6;
            color: var(--color-secondary-800);
        }

        /* Arabic language support */
        [lang="ar"] body {
            font-family: 'Cairo', 'Inter', system-ui, sans-serif;
        }

        .gradient-bg {
            background: var(--color-primary-500);
        }

        /* Modern Solid Color Variations */
        .gradient-primary {
            background: var(--color-primary-500);
        }

        .gradient-secondary {
            background: var(--color-secondary-500);
        }

        .gradient-accent {
            background: var(--color-accent-500);
        }

        .gradient-warm {
            background: var(--color-primary-400);
        }

        .gradient-cool {
            background: var(--color-primary-700);
        }

        .glass-effect {
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.1);
        }

        /* Custom animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(30px);
            }

            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes pulse {

            0%,
            100% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.05);
            }
        }

        .animate-fadeInUp {
            animation: fadeInUp 0.6s ease-out;
        }

        .animate-slideInRight {
            animation: slideInRight 0.6s ease-out;
        }

        .animate-pulse {
            animation: pulse 2s infinite;
        }

        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f5f9;
        }

        ::-webkit-scrollbar-thumb {
            background: linear-gradient(135deg, #14b8a6, #06b6d4);
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(135deg, #0f766e, #0891b2);
        }

        /* Enhanced focus states */
        .focus-ring {
            @apply focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-offset-2;
        }

        /* Glass morphism cards */
        .glass-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 8px 32px rgba(31, 38, 135, 0.37);
        }

        /* Hover effects */
        .hover-lift {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .hover-lift:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        }

        /* Typography */
        .font-display {
            font-family: 'Poppins', 'Cairo', system-ui, sans-serif;
            font-weight: 700;
            color: var(--color-secondary-900);
        }

        .font-body {
            font-family: 'Inter', 'Cairo', system-ui, sans-serif;
            font-weight: 400;
            color: var(--color-secondary-700);
        }

        /* Arabic typography */
        [lang="ar"] .font-display {
            font-family: 'Cairo', 'Poppins', system-ui, sans-serif;
        }

        [lang="ar"] .font-body {
            font-family: 'Cairo', 'Inter', system-ui, sans-serif;
        }

        /* RTL Support */
        [dir="rtl"] {
            direction: rtl;
            text-align: right;
        }

        [dir="rtl"] .space-x-2>*+* {
            margin-left: 0;
            margin-right: 0.5rem;
        }

        [dir="rtl"] .ml-3 {
            margin-left: 0;
            margin-right: 0.75rem;
        }

        [dir="rtl"] .mr-2 {
            margin-right: 0;
            margin-left: 0.5rem;
        }

        /* Modern Card Styles */
        .card-primary {
            background: #ffffff;
            border: 2px solid var(--color-primary-200);
            box-shadow: 0 4px 20px rgba(153, 189, 193, 0.1);
            border-radius: 16px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .card-primary:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 30px rgba(153, 189, 193, 0.2);
            border-color: var(--color-primary-300);
        }

        .card-secondary {
            background: #ffffff;
            border: 2px solid var(--color-secondary-200);
            box-shadow: 0 4px 20px rgba(173, 201, 205, 0.08);
            border-radius: 16px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .card-secondary:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 30px rgba(173, 201, 205, 0.15);
            border-color: var(--color-secondary-300);
        }

        .card-accent {
            background: #ffffff;
            border: 2px solid var(--color-accent-500);
            box-shadow: 0 4px 20px rgba(135, 228, 219, 0.1);
            border-radius: 16px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .card-accent:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 30px rgba(135, 228, 219, 0.2);
            border-color: var(--color-accent-600);
        }

        .card-warm {
            background: #ffffff;
            border: 2px solid var(--color-primary-400);
            box-shadow: 0 4px 20px rgba(112, 162, 167, 0.1);
            border-radius: 16px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .card-warm:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 30px rgba(112, 162, 167, 0.2);
            border-color: var(--color-primary-500);
        }

        .card-cool {
            background: #ffffff;
            border: 2px solid var(--color-primary-700);
            box-shadow: 0 4px 20px rgba(31, 108, 117, 0.1);
            border-radius: 16px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .card-cool:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 30px rgba(31, 108, 117, 0.2);
            border-color: var(--color-primary-800);
        }

        /* Status Cards */
        .card-success {
            background: #ffffff;
            border: 2px solid var(--color-success-500);
            box-shadow: 0 4px 20px rgba(34, 197, 94, 0.1);
            border-radius: 16px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .card-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 30px rgba(34, 197, 94, 0.2);
        }

        .card-warning {
            background: #ffffff;
            border: 2px solid var(--color-warning-500);
            box-shadow: 0 4px 20px rgba(245, 158, 11, 0.1);
            border-radius: 16px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .card-warning:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 30px rgba(245, 158, 11, 0.2);
        }

        .card-error {
            background: #ffffff;
            border: 2px solid var(--color-error-500);
            box-shadow: 0 4px 20px rgba(239, 68, 68, 0.1);
            border-radius: 16px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .card-error:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 30px rgba(239, 68, 68, 0.2);
        }

        /* Sidebar Layout Styles */
        .sidebar-layout {
            margin-left: 0;
            transition: margin-left 0.3s ease;
        }

        @media (min-width: 1024px) {
            .sidebar-layout {
                margin-left: 16rem;
            }
        }

        /* RTL Support for Sidebar */
        [dir="rtl"] .sidebar-layout {
            margin-left: 0;
            margin-right: 0;
        }

        @media (min-width: 1024px) {
            [dir="rtl"] .sidebar-layout {
                margin-left: 0;
                margin-right: 16rem;
            }
        }
    </style>
</head>

<body class="font-body antialiased" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}"
    lang="{{ app()->getLocale() }}">
    <div class="min-h-screen" x-data="{ sidebarOpen: false }">
        <!-- Sidebar -->
        <aside x-data="{ open: sidebarOpen }"
            class="fixed inset-y-0 left-0 z-50 w-64 bg-white border-r-2 border-primary-200 shadow-lg transition-all duration-300 hidden lg:block"
            :class="{ 'translate-x-0': sidebarOpen, '-translate-x-full': !sidebarOpen }"
            x-transition:enter="transition ease-out duration-300" x-transition:enter-start="-translate-x-full"
            x-transition:enter-end="translate-x-0" x-transition:leave="transition ease-in duration-300"
            x-transition:leave-start="translate-x-0" x-transition:leave-end="-translate-x-full">

            <!-- Sidebar Header -->
            <div class="flex items-center justify-between h-16 px-6 border-b border-primary-200">
                <div class="flex items-center space-x-3">
                    <div class="w-8 h-8 bg-primary-500 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                            </path>
                        </svg>
                    </div>
                    <span class="text-lg font-bold text-primary-600">{{ __('app.name') }}</span>
                </div>
                <button @click="sidebarOpen = false" class="text-gray-400 hover:text-gray-600 lg:hidden">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                </button>
            </div>

            <!-- Navigation Links -->
            <nav class="mt-6 px-3">
                <div class="space-y-1">
                    <x-sidebar-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 5a2 2 0 012-2h4a2 2 0 012 2v2H8V5z"></path>
                        </svg>
                        <span>{{ __('app.dashboard') }}</span>
                    </x-sidebar-nav-link>

                    <x-sidebar-nav-link :href="route('patients.index')" :active="request()->routeIs('patients.*')">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z">
                            </path>
                        </svg>
                        <span>{{ __('app.patients') }}</span>
                    </x-sidebar-nav-link>

                    <x-sidebar-nav-link :href="route('exams.index')" :active="request()->routeIs('exams.*')">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                            </path>
                        </svg>
                        <span>{{ __('app.exams') }}</span>
                    </x-sidebar-nav-link>

                    <x-sidebar-nav-link :href="route('glasses.index')" :active="request()->routeIs('glasses.*')">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span>{{ __('app.glasses') }}</span>
                    </x-sidebar-nav-link>

                    <x-sidebar-nav-link :href="route('sales.index')" :active="request()->routeIs('sales.*')">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z">
                            </path>
                        </svg>
                        <span>{{ __('app.sales') }}</span>
                    </x-sidebar-nav-link>

                    <x-sidebar-nav-link :href="route('expenses.index')" :active="request()->routeIs('expenses.*')">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                            </path>
                        </svg>
                        <span>{{ __('app.expenses') }}</span>
                    </x-sidebar-nav-link>

                    <x-sidebar-nav-link :href="route('reports.index')" :active="request()->routeIs('reports.*')">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                            </path>
                        </svg>
                        <span>{{ __('app.reports') }}</span>
                    </x-sidebar-nav-link>
                </div>
            </nav>

            <!-- User Section -->
            <div class="absolute bottom-0 left-0 right-0 p-4 border-t border-primary-200">
                <div class="flex items-center space-x-3 mb-4">
                    <div class="w-8 h-8 bg-primary-500 rounded-full flex items-center justify-center">
                        <span class="text-sm font-medium text-white">{{ substr(Auth::user()->name, 0, 1) }}</span>
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-medium text-gray-900">{{ Auth::user()->name }}</p>
                        <p class="text-xs text-gray-500">{{ Auth::user()->email }}</p>
                    </div>
                </div>

                <!-- Language Switcher -->
                <div class="mb-4">
                    <x-language-switcher :current-locale="app()->getLocale()" />
                </div>

                <!-- Logout -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                        class="w-full flex items-center px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-lg transition-colors duration-200">
                        <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1">
                            </path>
                        </svg>
                        {{ __('app.log_out') }}
                    </button>
                </form>
            </div>
        </aside>

        <!-- Mobile Sidebar Overlay -->
        <div x-show="sidebarOpen" x-transition:enter="transition-opacity ease-linear duration-300"
            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            x-transition:leave="transition-opacity ease-linear duration-300" x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0" class="fixed inset-0 z-40 bg-gray-600 bg-opacity-75 lg:hidden"
            @click="sidebarOpen = false"></div>

        <!-- Top Navigation Bar -->
        <nav
            class="bg-white border-b-2 border-primary-200 shadow-lg sticky top-0 z-40 transition-all duration-300 sidebar-layout">
            <div class="px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex items-center">
                        <!-- Mobile menu button -->
                        <button @click="sidebarOpen = true" class="text-gray-500 hover:text-gray-600 lg:hidden">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>

                        <!-- Page title -->
                        <div class="ml-4 lg:ml-0">
                            @if (isset($header))
                                <h1 class="text-xl font-semibold text-gray-900">{{ $header }}</h1>
                            @endif
                        </div>
                    </div>

                    <!-- Right side items -->
                    <div class="flex items-center space-x-4">
                        <!-- Layout Switcher -->
                        <div class="hidden sm:block">
                            <x-layout-switcher :current-layout="App\Services\LayoutService::getLayout()" />
                        </div>

                        <!-- Language Switcher (hidden on mobile) -->
                        <div class="hidden sm:block">
                            <x-language-switcher :current-locale="app()->getLocale()" />
                        </div>

                        <!-- User dropdown -->
                        <div class="relative">
                            <x-dropdown align="right" width="48">
                                <x-slot name="trigger">
                                    <button
                                        class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                                        <div class="flex items-center space-x-2">
                                            <div
                                                class="w-8 h-8 bg-primary-500 rounded-full flex items-center justify-center">
                                                <span
                                                    class="text-sm font-medium text-white">{{ substr(Auth::user()->name, 0, 1) }}</span>
                                            </div>
                                            <span class="hidden sm:block">{{ Auth::user()->name }}</span>
                                        </div>
                                        <div class="ml-1">
                                            <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg"
                                                viewBox="0 0 20 20">
                                                <path fill-rule="evenodd"
                                                    d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                    </button>
                                </x-slot>

                                <x-slot name="content">
                                    <x-dropdown-link :href="route('profile.edit')">
                                        {{ __('app.profile') }}
                                    </x-dropdown-link>

                                    <!-- Authentication -->
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <x-dropdown-link :href="route('logout')"
                                            onclick="event.preventDefault(); this.closest('form').submit();">
                                            {{ __('app.log_out') }}
                                        </x-dropdown-link>
                                    </form>
                                </x-slot>
                            </x-dropdown>
                        </div>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Page Content -->
        <main class="pb-12 bg-transparent sidebar-layout">
            <div class="min-h-screen">
                {{ $slot }}
            </div>
        </main>

        <!-- Notifications -->
        <x-notification type="success" />
        <x-notification type="error" />
        <x-notification type="info" />

        <!-- Loading Screen -->
        <x-loading />
    </div>
</body>

</html>