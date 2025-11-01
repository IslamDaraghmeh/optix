<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('app.view_glass') }}
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('glasses.edit', $glass) }}" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition-colors">
                    {{ __('app.edit') }}
                </a>
                <a href="{{ route('glasses.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-colors">
                    {{ __('app.back') }}
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <!-- Order Information -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('app.view_glass') }}</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">{{ __('app.sale_id') }}</dt>
                            <dd class="mt-1 text-sm text-gray-900">#{{ str_pad($glass->id, 6, '0', STR_PAD_LEFT) }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">{{ __('app.patient_name') }}</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $glass->patient->name }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">{{ __('app.patient_phone') }}</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $glass->patient->phone }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">{{ __('app.lens_type') }}</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $glass->lens_type }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">{{ __('app.frame_type') }}</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $glass->frame_type }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">{{ __('app.price') }}</dt>
                            <dd class="mt-1 text-sm text-gray-900 font-semibold">${{ number_format($glass->price, 2) }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">{{ __('app.status') }}</dt>
                            <dd class="mt-1">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $glass->status_badge }}">
                                    {{ ucfirst($glass->status) }}
                                </span>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">{{ __('app.date') }}</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $glass->created_at->format('M d, Y g:i A') }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">{{ __('app.last_updated') }}</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $glass->updated_at->format('M d, Y g:i A') }}</dd>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Status Update -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('app.update') }} {{ __('app.status') }}</h3>
                    <form method="POST" action="{{ route('glasses.status', $glass) }}" class="flex items-center space-x-4">
                        @csrf
                        @method('PATCH')

                        <select name="status" class="rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                            <option value="pending" {{ $glass->status == 'pending' ? 'selected' : '' }}>{{ __('app.pending') }}</option>
                            <option value="ready" {{ $glass->status == 'ready' ? 'selected' : '' }}>{{ __('app.ready') }}</option>
                            <option value="delivered" {{ $glass->status == 'delivered' ? 'selected' : '' }}>{{ __('app.delivered') }}</option>
                        </select>

                        <button type="submit" class="bg-primary-500 hover:bg-primary-600 text-white px-4 py-2 rounded-lg transition-colors">
                            {{ __('app.update') }} {{ __('app.status') }}
                        </button>
                    </form>
                </div>
            </div>

            <!-- Patient Information -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('app.patient_information') }}</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">{{ __('app.full_name') }}</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $glass->patient->name }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">{{ __('app.phone_number') }}</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $glass->patient->phone }}</dd>
                        </div>
                        @if($glass->patient->email)
                        <div>
                            <dt class="text-sm font-medium text-gray-500">{{ __('app.email_address') }}</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $glass->patient->email }}</dd>
                        </div>
                        @endif
                        @if($glass->patient->birth_date)
                        <div>
                            <dt class="text-sm font-medium text-gray-500">{{ __('app.birth_date') }}</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $glass->patient->birth_date->format('M d, Y') }}</dd>
                        </div>
                        @endif
                        @if($glass->patient->address)
                        <div class="md:col-span-2">
                            <dt class="text-sm font-medium text-gray-500">{{ __('app.address') }}</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $glass->patient->address }}</dd>
                        </div>
                        @endif
                    </div>

                    <div class="mt-6">
                        <a href="{{ route('patients.show', $glass->patient) }}" class="bg-teal-500 hover:bg-teal-600 text-white px-4 py-2 rounded-lg transition-colors">
                            {{ __('app.view_patient_profile') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
