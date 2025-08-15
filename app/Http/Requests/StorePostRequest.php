<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePostRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'content' => 'required|string|max:10000',
            'platforms' => 'required|array|min:1',
            'platforms.*' => 'in:twitter,linkedin,reddit',
            'type' => 'required|in:instant,queued,scheduled',
            'scheduled_at' => 'nullable|date|after:now',
            'media' => 'nullable|array|max:4',
            'media.*' => 'file|mimes:jpg,jpeg,png,gif,mp4,mov|max:10240', // 10MB max
        ];
    }

    public function messages()
    {
        return [
            'content.required' => 'El contenido de la publicación es obligatorio.',
            'content.max' => 'El contenido no puede exceder 10,000 caracteres.',
            'platforms.required' => 'Debes seleccionar al menos una red social.',
            'platforms.min' => 'Debes seleccionar al menos una red social.',
            'platforms.*.in' => 'Red social no válida.',
            'scheduled_at.after' => 'La fecha programada debe ser posterior a ahora.',
            'media.max' => 'Puedes subir máximo 4 archivos.',
            'media.*.mimes' => 'Solo se permiten archivos de imagen (jpg, png, gif) o video (mp4, mov).',
            'media.*.max' => 'Cada archivo no puede exceder 10MB.',
        ];
    }

    protected function prepareForValidation()
    {
        // Convertir platforms a array si viene como string
        if (is_string($this->platforms)) {
            $this->merge([
                'platforms' => explode(',', $this->platforms)
            ]);
        }
    }
}