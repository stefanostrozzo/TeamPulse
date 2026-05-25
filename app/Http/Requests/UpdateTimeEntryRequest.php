<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTimeEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Policy checked via $this->authorize() in controller
    }

    public function rules(): array
    {
        return [
            'started_at'  => 'required|date|before:ended_at',
            'ended_at'    => 'required|date|after:started_at',
            'description' => 'nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'started_at.before' => 'L\'ora di inizio deve essere prima della fine.',
            'ended_at.after'    => 'L\'ora di fine deve essere dopo l\'inizio.',
        ];
    }
}
