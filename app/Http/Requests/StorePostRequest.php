<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            'reddit_title' => [
                Rule::requiredIf(fn () => in_array('reddit', $this->input('platforms', []))),
                'nullable',
                'string',
                'max:300', // Reddit limita los títulos a 300 caracteres
            ],
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
            'reddit_title.required' => 'El título es obligatorio para publicaciones en Reddit.',
            'reddit_title.max' => 'El título no puede exceder 300 caracteres.',
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