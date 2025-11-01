<x-modal id="patientModal" title="{{ __('app.add_new_patient') }}" size="lg">
    <form method="POST" action="{{ route('patients.store') }}" id="patientForm" class="space-y-6">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Name -->
            <div class="space-y-2">
                <label for="modal_name" class="block text-sm font-medium text-gray-700 flex items-center">
                    <svg class="w-4 h-4 mr-2 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                    {{ __('app.patient_name_required') }}
                </label>
                <input type="text" name="name" id="modal_name" required placeholder="{{ __('app.enter_full_name') }}"
                    class="block w-full rounded-xl border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 transition-all duration-200">
                <div id="name_error" class="hidden flex items-center text-red-600 text-sm mt-1">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span></span>
                </div>
            </div>

            <!-- Phone -->
            <div class="space-y-2">
                <label for="modal_phone" class="block text-sm font-medium text-gray-700 flex items-center">
                    <svg class="w-4 h-4 mr-2 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z">
                        </path>
                    </svg>
                    {{ __('app.phone_number_required') }}
                </label>
                <input type="tel" name="phone" id="modal_phone" required
                    placeholder="{{ __('app.enter_phone_number') }}"
                    class="block w-full rounded-xl border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 transition-all duration-200">
                <div id="phone_error" class="hidden flex items-center text-red-600 text-sm mt-1">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span></span>
                </div>
            </div>

            <!-- Email -->
            <div class="space-y-2">
                <label for="modal_email" class="block text-sm font-medium text-gray-700 flex items-center">
                    <svg class="w-4 h-4 mr-2 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z">
                        </path>
                    </svg>
                    {{ __('app.email_address_optional') }}
                </label>
                <input type="email" name="email" id="modal_email" placeholder="{{ __('app.enter_email_address') }}"
                    class="block w-full rounded-xl border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 transition-all duration-200">
                <div id="email_error" class="hidden flex items-center text-red-600 text-sm mt-1">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span></span>
                </div>
            </div>

            <!-- Birth Date -->
            <div class="space-y-2">
                <label for="modal_birth_date" class="block text-sm font-medium text-gray-700 flex items-center">
                    <svg class="w-4 h-4 mr-2 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                        </path>
                    </svg>
                    {{ __('app.birth_date_optional') }}
                </label>
                <input type="date" name="birth_date" id="modal_birth_date"
                    class="block w-full rounded-xl border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 transition-all duration-200">
                <div id="birth_date_error" class="hidden flex items-center text-red-600 text-sm mt-1">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span></span>
                </div>
            </div>
        </div>

        <!-- Address -->
        <div class="space-y-2">
            <label for="modal_address" class="block text-sm font-medium text-gray-700 flex items-center">
                <svg class="w-4 h-4 mr-2 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
                {{ __('app.address_optional') }}
            </label>
            <textarea name="address" id="modal_address" rows="3" placeholder="{{ __('app.enter_address') }}"
                class="block w-full rounded-xl border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 transition-all duration-200"></textarea>
            <div id="address_error" class="hidden flex items-center text-red-600 text-sm mt-1">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span></span>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="flex justify-end space-x-4 pt-6 border-t border-gray-200">
            <button type="button" onclick="closeModal('patientModal')"
                class="flex items-center px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl font-medium transition-all duration-200 hover:shadow-md">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                    </path>
                </svg>
                {{ __('app.cancel') }}
            </button>
            <button type="submit" id="submitPatientBtn"
                class="flex items-center px-6 py-3 gradient-primary hover:shadow-xl transition-all duration-300 transform hover:scale-105 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-offset-2 text-white rounded-xl font-medium shadow-lg">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                <span id="submitText">{{ __('app.save_patient') }}</span>
                <svg id="loadingSpinner" class="hidden w-4 h-4 mr-2 animate-spin" fill="none" viewBox="0 0 24 24">
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
        const form = document.getElementById('patientForm');
        const submitBtn = document.getElementById('submitPatientBtn');
        const submitText = document.getElementById('submitText');
        const loadingSpinner = document.getElementById('loadingSpinner');

        form.addEventListener('submit', function (e) {
            e.preventDefault();

            // Show loading state
            submitBtn.disabled = true;
            submitText.textContent = '{{ __("app.creating_patient") }}';
            loadingSpinner.classList.remove('hidden');

            // Clear previous errors
            clearErrors();

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
                        showNotification('Patient created successfully!', 'success');

                        // Close modal
                        closeModal('patientModal');

                        // Reset form
                        form.reset();

                        // Reload page to show new patient
                        setTimeout(() => {
                            window.location.reload();
                        }, 1000);
                    } else {
                        // Show validation errors
                        if (data.errors) {
                            showErrors(data.errors);
                        } else {
                            showNotification(data.message || 'An error occurred', 'error');
                        }
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('An error occurred while creating the patient', 'error');
                })
                .finally(() => {
                    // Reset button state
                    submitBtn.disabled = false;
                    submitText.textContent = '{{ __("app.save_patient") }}';
                    loadingSpinner.classList.add('hidden');
                });
        });

        function clearErrors() {
            const errorElements = document.querySelectorAll('[id$="_error"]');
            errorElements.forEach(element => {
                element.classList.add('hidden');
            });
        }

        function showErrors(errors) {
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