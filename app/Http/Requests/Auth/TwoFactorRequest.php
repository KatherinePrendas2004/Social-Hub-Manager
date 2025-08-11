<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class TwoFactorRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'enable' => 'required|in:true,false',
            'two_factor_code' => 'nullable|string|size:6|regex:/^[0-9]{6}$/'
        ];
    }

    /**
     * Get custom error messages.
     */
    public function messages(): array
    {
        return [
            'enable.required' => 'El parámetro enable es requerido',
            'enable.in' => 'El parámetro enable debe ser true o false',
            'two_factor_code.size' => 'El código debe tener exactamente 6 dígitos',
            'two_factor_code.regex' => 'El código debe contener solo números'
        ];
    }
}