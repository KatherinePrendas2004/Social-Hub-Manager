<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\PublishSchedule;

class StorePublishScheduleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation()
    {
        // Asegurar que platforms sea un array, incluso si viene vacío
        if (!$this->has('platforms') || empty($this->platforms)) {
            $this->merge(['platforms' => null]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'day_of_week' => [
                'required',
                'string',
                'in:monday,tuesday,wednesday,thursday,friday,saturday,sunday'
            ],
            'time' => [
                'required',
                'date_format:H:i',
                function ($attribute, $value, $fail) {
                    // Verificar que no exista ya este horario para el usuario
                    $exists = PublishSchedule::where('user_id', auth()->id())
                        ->where('day_of_week', $this->day_of_week)
                        ->where('time', $value)
                        ->exists();
                    
                    if ($exists) {
                        $dayName = PublishSchedule::DAYS_OF_WEEK[$this->day_of_week];
                        $fail("Ya existe un horario configurado para {$dayName} a las {$value}");
                    }
                }
            ],
            'platforms' => [
                'nullable',
                'array'
            ],
            'platforms.*' => [
                'string',
                'in:twitter,linkedin,reddit'
            ]
        ];
    }

    /**
     * Get custom validation messages
     */
    public function messages(): array
    {
        return [
            'day_of_week.required' => 'El día de la semana es obligatorio',
            'day_of_week.in' => 'El día de la semana seleccionado no es válido',
            'time.required' => 'La hora es obligatoria',
            'time.date_format' => 'El formato de hora debe ser HH:MM (ejemplo: 14:30)',
            'platforms.array' => 'Las plataformas deben ser un array',
            'platforms.*.in' => 'La plataforma seleccionada no es válida'
        ];
    }

    /**
     * Get custom attribute names for validation errors
     */
    public function attributes(): array
    {
        return [
            'day_of_week' => 'día de la semana',
            'time' => 'hora',
            'platforms' => 'plataformas'
        ];
    }
}