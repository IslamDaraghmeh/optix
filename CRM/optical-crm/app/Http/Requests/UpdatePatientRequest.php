<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePatientRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        $patientId = $this->route('patient')->id;

        return [
            'name' => 'required|string|max:255',
            'phone' => 'required|string|unique:patients,phone,' . $patientId . '|max:20',
            'email' => 'nullable|email|unique:patients,email,' . $patientId . '|max:255',
            'birth_date' => 'nullable|date|before:today',
            'address' => 'nullable|string|max:1000',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages()
    {
        return [
            'name.required' => 'Patient name is required.',
            'phone.required' => 'Phone number is required.',
            'phone.unique' => 'This phone number is already registered.',
            'email.email' => 'Please enter a valid email address.',
            'email.unique' => 'This email address is already registered.',
            'birth_date.before' => 'Birth date must be before today.',
        ];
    }
}
