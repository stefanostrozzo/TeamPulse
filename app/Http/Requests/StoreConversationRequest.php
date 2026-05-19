<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreConversationRequest extends FormRequest
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
            'participant_ids' => ['required', 'array', 'min:1'],
            'participant_ids.*' => ['required', 'exists:users,id'],
            'is_group' => ['boolean'],
            'name' => ['required_if:is_group,true', 'nullable', 'string', 'max:255'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $ids = (array) $this->input('participant_ids', []);
            $others = array_values(array_filter($ids, fn ($id) => (int) $id !== (int) $this->user()->id));

            if (count($others) === 0) {
                $validator->errors()->add('participant_ids', 'A conversation requires at least one participant other than yourself.');
            }
        });
    }
}
