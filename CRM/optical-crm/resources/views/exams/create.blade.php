<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('app.add_new_eye_exam') }}
            </h2>
            <a href="{{ route('exams.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-colors">
                {{ __('app.back_to_exams') }}
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ route('exams.store') }}" class="space-y-8">
                        @csrf

                        <!-- Patient Selection -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="md:col-span-2">
                                <label for="patient_id" class="block text-sm font-medium text-gray-700">{{ __('app.patient') }} *</label>
                                <select name="patient_id" id="patient_id" required
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('patient_id') border-red-300 @enderror">
                                    <option value="">{{ __('app.select_patient') }}</option>
                                    @foreach($patients as $patient)
                                        <option value="{{ $patient->id }}" {{ old('patient_id', $selectedPatientId) == $patient->id ? 'selected' : '' }}>
                                            {{ $patient->name }} - {{ $patient->phone }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('patient_id')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="exam_date" class="block text-sm font-medium text-gray-700">{{ __('app.exam_date') }} *</label>
                                <input type="date" name="exam_date" id="exam_date" value="{{ old('exam_date', date('Y-m-d')) }}" required
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('exam_date') border-red-300 @enderror">
                                @error('exam_date')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Eye Exam Measurements -->
                        <div class="border-t pt-8">
                            <h3 class="text-lg font-medium text-gray-900 mb-6">{{ __('app.eye_measurements') }}</h3>

                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                                <!-- Right Eye -->
                                <div class="bg-blue-50 p-6 rounded-lg">
                                    <h4 class="text-md font-medium text-blue-900 mb-4">{{ __('app.right_eye_od') }}</h4>
                                    <div class="grid grid-cols-3 gap-4">
                                        <div>
                                            <label for="right_eye_sphere" class="block text-sm font-medium text-gray-700">{{ __('app.sphere') }}</label>
                                            <input type="number" step="0.25" name="right_eye_sphere" id="right_eye_sphere" value="{{ old('right_eye_sphere') }}"
                                                   placeholder="±0.00"
                                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('right_eye_sphere') border-red-300 @enderror">
                                            @error('right_eye_sphere')
                                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>
                                        <div>
                                            <label for="right_eye_cylinder" class="block text-sm font-medium text-gray-700">{{ __('app.cylinder') }}</label>
                                            <input type="number" step="0.25" name="right_eye_cylinder" id="right_eye_cylinder" value="{{ old('right_eye_cylinder') }}"
                                                   placeholder="±0.00"
                                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('right_eye_cylinder') border-red-300 @enderror">
                                            @error('right_eye_cylinder')
                                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>
                                        <div>
                                            <label for="right_eye_axis" class="block text-sm font-medium text-gray-700">{{ __('app.axis') }}</label>
                                            <input type="number" min="0" max="180" name="right_eye_axis" id="right_eye_axis" value="{{ old('right_eye_axis') }}"
                                                   placeholder="0°"
                                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('right_eye_axis') border-red-300 @enderror">
                                            @error('right_eye_axis')
                                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <!-- Left Eye -->
                                <div class="bg-green-50 p-6 rounded-lg">
                                    <h4 class="text-md font-medium text-green-900 mb-4">{{ __('app.left_eye_os') }}</h4>
                                    <div class="grid grid-cols-3 gap-4">
                                        <div>
                                            <label for="left_eye_sphere" class="block text-sm font-medium text-gray-700">{{ __('app.sphere') }}</label>
                                            <input type="number" step="0.25" name="left_eye_sphere" id="left_eye_sphere" value="{{ old('left_eye_sphere') }}"
                                                   placeholder="±0.00"
                                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('left_eye_sphere') border-red-300 @enderror">
                                            @error('left_eye_sphere')
                                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>
                                        <div>
                                            <label for="left_eye_cylinder" class="block text-sm font-medium text-gray-700">{{ __('app.cylinder') }}</label>
                                            <input type="number" step="0.25" name="left_eye_cylinder" id="left_eye_cylinder" value="{{ old('left_eye_cylinder') }}"
                                                   placeholder="±0.00"
                                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('left_eye_cylinder') border-red-300 @enderror">
                                            @error('left_eye_cylinder')
                                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>
                                        <div>
                                            <label for="left_eye_axis" class="block text-sm font-medium text-gray-700">{{ __('app.axis') }}</label>
                                            <input type="number" min="0" max="180" name="left_eye_axis" id="left_eye_axis" value="{{ old('left_eye_axis') }}"
                                                   placeholder="0°"
                                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('left_eye_axis') border-red-300 @enderror">
                                            @error('left_eye_axis')
                                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Notes -->
                        <div class="border-t pt-8">
                            <label for="notes" class="block text-sm font-medium text-gray-700">{{ __('app.exam_notes') }}</label>
                            <textarea name="notes" id="notes" rows="4"
                                      placeholder="{{ __('app.additional_notes_exam') }}"
                                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('notes') border-red-300 @enderror">{{ old('notes') }}</textarea>
                            @error('notes')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Form Actions -->
                        <div class="flex justify-end space-x-4 pt-6 border-t">
                            <a href="{{ route('exams.index') }}"
                               class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-6 py-2 rounded-lg transition-colors">
                                {{ __('app.cancel') }}
                            </a>
                            <button type="submit"
                                    class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg transition-colors">
                                {{ __('app.save_eye_exam') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Help Section -->
            <div class="mt-6 bg-gray-50 p-6 rounded-lg">
                <h4 class="text-sm font-medium text-gray-900 mb-3">{{ __('app.prescription_guide') }}</h4>
                <div class="text-sm text-gray-600 space-y-2">
                    <p><strong>{{ __('app.sphere') }} (SPH):</strong> {{ __('app.sphere_description') }}</p>
                    <p><strong>{{ __('app.cylinder') }} (CYL):</strong> {{ __('app.cylinder_description') }}</p>
                    <p><strong>{{ __('app.axis') }}:</strong> {{ __('app.axis_description') }}</p>
                    <p class="text-xs text-gray-500">{{ __('app.values_increment_note') }}</p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
