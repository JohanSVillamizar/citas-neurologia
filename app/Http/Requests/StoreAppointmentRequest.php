<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAppointmentRequest extends FormRequest
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
            'doctor_id' => 'required|exists:doctors,id',
            'patient_name' => 'required|string|max:255',
            'patient_email' => 'required|email|max:255',
            'patient_phone' => 'required|string|max:20',
            'patient_id_number' => 'required|string|max:20',
            'patient_birth_date' => 'required|date|before:today',
            'reason' => 'nullable|string|max:500',
            'appointment_date' => 'required|date|after:now',
        ];
    }

        public function messages(): array
    {
        return [
            'doctor_id.required' => 'Debe seleccionar un médico',
            'doctor_id.exists' => 'El médico seleccionado no existe',
            'patient_name.required' => 'El nombre del paciente es obligatorio',
            'patient_email.required' => 'El correo electrónico es obligatorio',
            'patient_email.email' => 'Debe ingresar un correo válido',
            'patient_phone.required' => 'El teléfono es obligatorio',
            'patient_id_number.required' => 'El número de identificación es obligatorio',
            'patient_birth_date.required' => 'La fecha de nacimiento es obligatoria',
            'patient_birth_date.before' => 'La fecha de nacimiento debe ser anterior a hoy',
            'appointment_date.required' => 'Debe seleccionar una fecha para la cita',
            'appointment_date.after' => 'La cita debe ser en una fecha futura',
        ];
    }
}
