<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('app.create_glass') }}
            </h2>
            <a href="{{ route('glasses.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-colors">
                {{ __('app.back') }}
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ route('glasses.store') }}" class="space-y-6">
                        @csrf

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Patient Selection -->
                            <div class="md:col-span-2">
                                <label for="patient_id" class="block text-sm font-medium text-gray-700">{{ __('app.patient') }} *</label>
                                <select name="patient_id" id="patient_id" required
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 @error('patient_id') border-red-300 @enderror">
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

                            <!-- Lens Type -->
                            <div>
                                <label for="lens_type" class="block text-sm font-medium text-gray-700">{{ __('app.lens_type') }} *</label>
                                <input type="text" name="lens_type" id="lens_type" value="{{ old('lens_type') }}" required
                                       placeholder="e.g., Single Vision, Progressive, Bifocal"
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 @error('lens_type') border-red-300 @enderror">
                                @error('lens_type')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Frame Type -->
                            <div>
                                <label for="frame_type" class="block text-sm font-medium text-gray-700">{{ __('app.frame_type') }} *</label>
                                <input type="text" name="frame_type" id="frame_type" value="{{ old('frame_type') }}" required
                                       placeholder="e.g., Metal, Plastic, Titanium"
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 @error('frame_type') border-red-300 @enderror">
                                @error('frame_type')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Price -->
                            <div>
                                <label for="price" class="block text-sm font-medium text-gray-700">{{ __('app.price') }} *</label>
                                <input type="number" step="0.01" name="price" id="price" value="{{ old('price') }}" required
                                       placeholder="0.00"
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 @error('price') border-red-300 @enderror">
                                @error('price')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Status -->
                            <div>
                                <label for="status" class="block text-sm font-medium text-gray-700">{{ __('app.status') }}</label>
                                <select name="status" id="status"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 @error('status') border-red-300 @enderror">
                                    <option value="pending" {{ old('status') == 'pending' ? 'selected' : '' }}>{{ __('app.pending') }}</option>
                                    <option value="ready" {{ old('status') == 'ready' ? 'selected' : '' }}>{{ __('app.ready') }}</option>
                                    <option value="delivered" {{ old('status') == 'delivered' ? 'selected' : '' }}>{{ __('app.delivered') }}</option>
                                </select>
                                @error('status')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="flex justify-end space-x-4">
                            <a href="{{ route('glasses.index') }}"
                               class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-6 py-2 rounded-lg transition-colors">
                                {{ __('app.cancel') }}
                            </a>
                            <button type="submit"
                                    class="bg-primary-500 hover:bg-primary-600 text-white px-6 py-2 rounded-lg transition-colors">
                                {{ __('app.create_glass') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
