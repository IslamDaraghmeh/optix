<x-modal id="examModal" title="{{ __('app.new_exam') }}" size="xl">
    <form method="POST" action="{{ route('exams.store') }}" id="examForm" class="space-y-6">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Patient Selection -->
            <div class="md:col-span-2 space-y-2">
                <label for="modal_patient_id" class="block text-sm font-medium text-gray-700 flex items-center">
                    <svg class="w-4 h-4 mr-2 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z">
                        </path>
                    </svg>
                    {{ __('app.patient') }} *
                </label>
                <select name="patient_id" id="modal_patient_id" required
                    class="block w-full rounded-xl border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 transition-all duration-200">
                    <option value="">{{ __('app.select_patient') }}</option>
                    @foreach(\App\Models\Patient::orderBy('name')->get() as $patient)
                        <option value="{{ $patient->id }}">{{ $patient->name }} - {{ $patient->phone }}</option>
                    @endforeach
                </select>
                <div id="patient_id_error" class="hidden flex items-center text-red-600 text-sm mt-1">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span></span>
                </div>
            </div>

            <!-- Exam Date -->
            <div class="space-y-2">
                <label for="modal_exam_date" class="block text-sm font-medium text-gray-700 flex items-center">
                    <svg class="w-4 h-4 mr-2 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                        </path>
                    </svg>
                    {{ __('app.exam_date') }} *
                </label>
                <input type="date" name="exam_date" id="modal_exam_date" value="{{ date('Y-m-d') }}" required
                    class="block w-full rounded-xl border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 transition-all duration-200">
                <div id="exam_date_error" class="hidden flex items-center text-red-600 text-sm mt-1">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span></span>
                </div>
            </div>
        </div>

        <!-- Eye Prescription -->
        <div class="space-y-6">
            <h4 class="text-lg font-semibold text-gray-900 border-b border-gray-200 pb-2">
                {{ __('app.eye_prescription') }}</h4>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Right Eye -->
                <div class="space-y-4">
                    <h5 class="text-md font-medium text-gray-700 flex items-center">
                        <svg class="w-4 h-4 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        {{ __('app.right_eye_od') }}
                    </h5>
                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-600">{{ __('app.sphere') }}</label>
                            <input type="number" step="0.25" name="right_eye_sphere" placeholder="0.00"
                                class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 transition-all duration-200">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600">{{ __('app.cylinder') }}</label>
                            <input type="number" step="0.25" name="right_eye_cylinder" placeholder="0.00"
                                class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 transition-all duration-200">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600">{{ __('app.axis') }}</label>
                            <input type="number" name="right_eye_axis" placeholder="0"
                                class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 transition-all duration-200">
                        </div>
                    </div>
                </div>

                <!-- Left Eye -->
                <div class="space-y-4">
                    <h5 class="text-md font-medium text-gray-700 flex items-center">
                        <svg class="w-4 h-4 mr-2 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        {{ __('app.left_eye_os') }}
                    </h5>
                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-600">{{ __('app.sphere') }}</label>
                            <input type="number" step="0.25" name="left_eye_sphere" placeholder="0.00"
                                class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 transition-all duration-200">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600">{{ __('app.cylinder') }}</label>
                            <input type="number" step="0.25" name="left_eye_cylinder" placeholder="0.00"
                                class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 transition-all duration-200">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600">{{ __('app.axis') }}</label>
                            <input type="number" name="left_eye_axis" placeholder="0"
                                class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 transition-all duration-200">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Notes -->
        <div class="space-y-2">
            <label for="modal_notes" class="block text-sm font-medium text-gray-700 flex items-center">
                <svg class="w-4 h-4 mr-2 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                    </path>
                </svg>
                Notes
            </label>
            <textarea name="notes" id="modal_notes" rows="3" placeholder="Additional notes about the exam..."
                class="block w-full rounded-xl border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 transition-all duration-200"></textarea>
        </div>

        <!-- Form Actions -->
        <div class="flex justify-end space-x-4 pt-6 border-t border-gray-200">
            <button type="button" onclick="closeModal('examModal')"
                class="flex items-center px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl font-medium transition-all duration-200 hover:shadow-md">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                    </path>
                </svg>
                Cancel
            </button>
            <button type="submit" id="submitExamBtn"
                class="flex items-center px-6 py-3 gradient-secondary hover:shadow-xl transition-all duration-300 transform hover:scale-105 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-offset-2 text-white rounded-xl font-medium shadow-lg">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                <span id="submitExamText">Create Exam</span>
                <svg id="loadingExamSpinner" class="hidden w-4 h-4 mr-2 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor"
                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                    </path>
                </svg>
            </button>
        </div>
    </form>
</x-modal>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('examForm');
        const submitBtn = document.getElementById('submitExamBtn');
        const submitText = document.getElementById('submitExamText');
        const loadingSpinner = document.getElementById('loadingExamSpinner');

        if (form) {
            form.addEventListener('submit', function (e) {
                e.preventDefault();

                // Show loading state
                submitBtn.disabled = true;
                submitText.textContent = 'Creating...';
                loadingSpinner.classList.remove('hidden');

                // Clear previous errors
                clearExamErrors();

                // Get form data
                const formData = new FormData(form);

                // Submit via fetch
                fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Show success message
                            showNotification('Exam created successfully!', 'success');

                            // Close modal
                            closeModal('examModal');

                            // Reset form
                            form.reset();

                            // Reload page to show new exam
                            setTimeout(() => {
                                window.location.reload();
                            }, 1000);
                        } else {
                            // Show validation errors
                            if (data.errors) {
                                showExamErrors(data.errors);
                            } else {
                                showNotification(data.message || 'An error occurred', 'error');
                            }
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showNotification('An error occurred while creating the exam', 'error');
                    })
                    .finally(() => {
                        // Reset button state
                        submitBtn.disabled = false;
                        submitText.textContent = 'Create Exam';
                        loadingSpinner.classList.add('hidden');
                    });
            });
        }

        function clearExamErrors() {
            const errorElements = document.querySelectorAll('[id$="_error"]');
            errorElements.forEach(element => {
                element.classList.add('hidden');
            });
        }

        function showExamErrors(errors) {
            Object.keys(errors).forEach(field => {
                const errorElement = document.getElementById(field + '_error');
                if (errorElement) {
                    errorElement.querySelector('span').textContent = errors[field][0];
                    errorElement.classList.remove('hidden');
                }
            });
        }

        function showNotification(message, type) {
            // Create notification element
            const notification = document.createElement('div');
            notification.className = `fixed top-4 right-4 z-50 max-w-sm w-full transform transition-all duration-300`;
            notification.innerHTML = `
            <div class="bg-white rounded-xl shadow-2xl border-l-4 ${type === 'success' ? 'border-primary-500' : 'border-red-500'} p-4">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <svg class="w-6 h-6 ${type === 'success' ? 'text-green-500' : 'text-red-500'}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="${type === 'success' ? 'M5 13l4 4L19 7' : 'M6 18L18 6M6 6l12 12'}"></path>
                        </svg>
                    </div>
                    <div class="ml-3 flex-1">
                        <p class="text-sm font-medium ${type === 'success' ? 'text-primary-800' : 'text-red-800'}">${message}</p>
                    </div>
                    <div class="ml-4 flex-shrink-0">
                        <button onclick="this.parentElement.parentElement.parentElement.parentElement.remove()" class="inline-flex ${type === 'success' ? 'text-green-400 hover:text-primary-600' : 'text-red-400 hover:text-red-600'} focus:outline-none">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        `;

            document.body.appendChild(notification);

            // Auto remove after 5 seconds
            setTimeout(() => {
                notification.remove();
            }, 5000);
        }
    });
</script>