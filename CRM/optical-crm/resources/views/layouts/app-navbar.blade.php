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

    <!-- Custom Styles From Pages -->
    @stack('styles')

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

        [dir="rtl"] .space-x-1>*+* {
            margin-left: 0;
            margin-right: 0.25rem;
        }

        [dir="rtl"] .space-x-2>*+* {
            margin-left: 0;
            margin-right: 0.5rem;
        }

        [dir="rtl"] .space-x-3>*+* {
            margin-left: 0;
            margin-right: 0.75rem;
        }

        [dir="rtl"] .space-x-4>*+* {
            margin-left: 0;
            margin-right: 1rem;
        }

        [dir="rtl"] .ml-1 {
            margin-left: 0;
            margin-right: 0.25rem;
        }

        [dir="rtl"] .ml-2 {
            margin-left: 0;
            margin-right: 0.5rem;
        }

        [dir="rtl"] .ml-3 {
            margin-left: 0;
            margin-right: 0.75rem;
        }

        [dir="rtl"] .ml-4 {
            margin-left: 0;
            margin-right: 1rem;
        }

        [dir="rtl"] .mr-1 {
            margin-right: 0;
            margin-left: 0.25rem;
        }

        [dir="rtl"] .mr-2 {
            margin-right: 0;
            margin-left: 0.5rem;
        }

        [dir="rtl"] .mr-3 {
            margin-right: 0;
            margin-left: 0.75rem;
        }

        [dir="rtl"] .mr-4 {
            margin-right: 0;
            margin-left: 1rem;
        }

        /* RTL Flex and Alignment */
        [dir="rtl"] .justify-end {
            justify-content: flex-start;
        }

        [dir="rtl"] .justify-start {
            justify-content: flex-end;
        }

        [dir="rtl"] .text-left {
            text-align: right;
        }

        [dir="rtl"] .text-right {
            text-align: left;
        }

        [dir="rtl"] .float-left {
            float: right;
        }

        [dir="rtl"] .float-right {
            float: left;
        }

        /* RTL Inline Flex for SVG Icons */
        [dir="rtl"] .inline {
            display: inline-flex;
            flex-direction: row-reverse;
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
    </style>
</head>

<body class="font-body antialiased" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}"
    lang="{{ app()->getLocale() }}">
    <div class="min-h-screen">
        @include('layouts.navigation')

        <!-- Page Heading -->
        @if (isset($header))
            <header class="bg-gradient-to-r from-primary-700 via-primary-600 to-primary-700 shadow-2xl border-b-4 border-primary-800/20">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    <div class="text-white">
                        {{ $header }}
                    </div>
                </div>
            </header>
        @endif

        <!-- Page Content -->
        <main class="pb-12 bg-gradient-to-br from-gray-50 via-primary-50/30 to-secondary-50/20">
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

    <!-- Custom Scripts From Pages -->
    @stack('scripts')
</body>

</html>