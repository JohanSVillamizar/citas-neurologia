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
            'license_number' => 'required|string|unique:doctors,license_number',
            'bio' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
            'email' => 'required|email|unique:doctors,email',
            'is_active' => 'boolean',
        ];
    }
}
