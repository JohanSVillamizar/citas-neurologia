<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDoctorRequest extends FormRequest
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
        $doctorId = $this->route('doctor')->id;

        return [
            'name' => 'sometimes|string|max:255',
            'specialty' => 'sometimes|string|max:255',
            'license_number' => 'sometimes|string|unique:doctors,license_number,' . $doctorId,
            'bio' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
            'email' => 'sometimes|email|unique:doctors,email,' . $doctorId,
            'is_active' => 'boolean',
        ];
    }
}
