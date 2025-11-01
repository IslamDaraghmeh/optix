<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('app.exam_details') }} - {{ $exam->patient->name }}
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('exams.prescription', $exam) }}"
                    class="bg-primary-500 hover:bg-primary-600 text-white px-4 py-2 rounded-lg transition-colors"
                    target="_blank">
                    {{ __('app.print_prescription') }}
                </a>
                <a href="{{ route('exams.edit', $exam) }}"
                    class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition-colors">
                    {{ __('app.edit_exam') }}
                </a>
                <a href="{{ route('exams.index') }}"
                    class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-colors">
                    {{ __('app.back_to_exams') }}
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <!-- Exam Information -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('app.exam_information') }}</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">{{ __('app.exam_id') }}</dt>
                            <dd class="mt-1 text-sm text-gray-900">#{{ str_pad($exam->id, 6, '0', STR_PAD_LEFT) }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">{{ __('app.patient_name') }}</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $exam->patient->name }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">{{ __('app.patient_phone') }}</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $exam->patient->phone }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">{{ __('app.exam_date') }}</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $exam->exam_date->format('M d, Y') }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">{{ __('app.created') }}</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $exam->created_at->format('M d, Y g:i A') }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">{{ __('app.last_updated') }}</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $exam->updated_at->format('M d, Y g:i A') }}</dd>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Eye Prescription -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-6">{{ __('app.eye_prescription') }}</h3>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                        <!-- Right Eye -->
                        <div class="border rounded-lg p-6">
                            <h4 class="text-md font-semibold text-gray-900 mb-4 text-center">{{ __('app.right_eye_od') }}</h4>
                            <div class="grid grid-cols-3 gap-4">
                                <div class="text-center">
                                    <dt class="text-sm font-medium text-gray-500">{{ __('app.sphere') }}</dt>
                                    <dd class="mt-1 text-lg font-semibold text-gray-900">
                                        {{ $exam->right_eye_sphere ?? 'N/A' }}
                                    </dd>
                                </div>
                                <div class="text-center">
                                    <dt class="text-sm font-medium text-gray-500">{{ __('app.cylinder') }}</dt>
                                    <dd class="mt-1 text-lg font-semibold text-gray-900">
                                        {{ $exam->right_eye_cylinder ?? 'N/A' }}
                                    </dd>
                                </div>
                                <div class="text-center">
                                    <dt class="text-sm font-medium text-gray-500">{{ __('app.axis') }}</dt>
                                    <dd class="mt-1 text-lg font-semibold text-gray-900">
                                        {{ $exam->right_eye_axis ?? 'N/A' }}
                                    </dd>
                                </div>
                            </div>
                        </div>

                        <!-- Left Eye -->
                        <div class="border rounded-lg p-6">
                            <h4 class="text-md font-semibold text-gray-900 mb-4 text-center">{{ __('app.left_eye_os') }}</h4>
                            <div class="grid grid-cols-3 gap-4">
                                <div class="text-center">
                                    <dt class="text-sm font-medium text-gray-500">{{ __('app.sphere') }}</dt>
                                    <dd class="mt-1 text-lg font-semibold text-gray-900">
                                        {{ $exam->left_eye_sphere ?? 'N/A' }}
                                    </dd>
                                </div>
                                <div class="text-center">
                                    <dt class="text-sm font-medium text-gray-500">{{ __('app.cylinder') }}</dt>
                                    <dd class="mt-1 text-lg font-semibold text-gray-900">
                                        {{ $exam->left_eye_cylinder ?? 'N/A' }}
                                    </dd>
                                </div>
                                <div class="text-center">
                                    <dt class="text-sm font-medium text-gray-500">{{ __('app.axis') }}</dt>
                                    <dd class="mt-1 text-lg font-semibold text-gray-900">
                                        {{ $exam->left_eye_axis ?? 'N/A' }}
                                    </dd>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Notes -->
            @if($exam->notes)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('app.notes') }}</h3>
                        <div class="prose max-w-none">
                            <p class="text-gray-700 whitespace-pre-wrap">{{ $exam->notes }}</p>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Patient Information -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('app.patient_information') }}</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">{{ __('app.full_name') }}</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $exam->patient->name }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">{{ __('app.phone_number') }}</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $exam->patient->phone }}</dd>
                        </div>
                        @if($exam->patient->email)
                            <div>
                                <dt class="text-sm font-medium text-gray-500">{{ __('app.email_address') }}</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $exam->patient->email }}</dd>
                            </div>
                        @endif
                        @if($exam->patient->birth_date)
                            <div>
                                <dt class="text-sm font-medium text-gray-500">{{ __('app.birth_date') }}</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $exam->patient->birth_date->format('M d, Y') }}
                                </dd>
                            </div>
                        @endif
                        @if($exam->patient->address)
                            <div class="md:col-span-2">
                                <dt class="text-sm font-medium text-gray-500">{{ __('app.address') }}</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $exam->patient->address }}</dd>
                            </div>
                        @endif
                    </div>

                    <div class="mt-6 flex space-x-4">
                        <a href="{{ route('patients.show', $exam->patient) }}"
                            class="bg-teal-500 hover:bg-teal-600 text-white px-4 py-2 rounded-lg transition-colors">
                            {{ __('app.view_patient_profile') }}
                        </a>
                        <a href="{{ route('glasses.create', ['patient' => $exam->patient->id]) }}"
                            class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition-colors">
                            {{ __('app.order_glasses') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
