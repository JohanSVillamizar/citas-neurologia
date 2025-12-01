<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDoctorRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'specialty' => 'required|string|max:255',
            'license_number' => [
                'required',
                'string',
                'unique:doctors,license_number',
                'regex:/^MED-\d{6}$/', // Debe ser MED- seguido de 6 dígitos
            ],
            'bio' => 'nullable|string',
            'phone' => [
                'nullable',
                'string',
                'regex:/^\d{3}-\d{3}-\d{4}$/', // Formato: 300-123-4567
            ],
            'email' => 'required|email|unique:doctors,email',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get custom error messages.
     */
    public function messages(): array
    {
        return [
            'license_number.regex' => 'El número de licencia debe tener el formato MED- seguido de 6 dígitos. Ej: MED-123456',
            'phone.regex' => 'El teléfono debe tener el formato XXX-XXX-XXXX. Ej: 300-123-4567',
            'photo.image' => 'El archivo debe ser una imagen.',
            'photo.mimes' => 'La foto debe ser JPG, PNG o JPEG.',
            'photo.max' => 'La foto no debe exceder 2MB.',
            'name.required' => 'El nombre del doctor es requerido.',
            'specialty.required' => 'La especialidad es requerida.',
            'email.required' => 'El correo es requerido.',
            'email.email' => 'El correo debe ser válido.',
            'email.unique' => 'Este correo ya está registrado.',
            'license_number.unique' => 'Este número de licencia ya está registrado.',
        ];
    }
}
