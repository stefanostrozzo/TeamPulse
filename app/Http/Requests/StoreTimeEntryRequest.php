<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTimeEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Controller handles on-behalf policy check
    }

    public function rules(): array
    {
        return [
            'started_at'  => 'required|date|before:ended_at',
            'ended_at'    => 'required|date|after:started_at',
            'description' => 'nullable|string|max:500',
            'user_id'     => 'nullable|exists:users,id', // for on-behalf logging
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
