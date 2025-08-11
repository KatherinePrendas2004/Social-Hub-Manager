<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'email' => 'required|email',
            'password' => 'required|string',
            'two_factor_code' => 'nullable|string|min:6|max:8',
        ];
    }

    public function messages()
    {
        return [
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'El correo electrónico debe ser válido.',
            'password.required' => 'La contraseña es obligatoria.',
            'two_factor_code.min' => 'El código debe tener al menos 6 dígitos.',
            'two_factor_code.max' => 'El código no debe exceder 8 caracteres.',
        ];
    }
}