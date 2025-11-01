@props(['title', 'tableId'])

<div class="card-primary overflow-hidden rounded-2xl shadow-2xl border-2 border-primary-100">
    <div class="bg-gradient-to-r from-primary-50 to-secondary-50 px-6 py-4 border-b-2 border-primary-100">
        <h3 class="font-display text-xl text-primary-800">
            <svg class="w-6 h-6 inline mr-2 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            {{ $title }}
        </h3>
    </div>
    <div class="p-6 bg-white">
        {{ $slot }}
    </div>
</div>
