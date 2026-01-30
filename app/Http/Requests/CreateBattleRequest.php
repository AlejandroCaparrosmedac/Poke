<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateBattleRequest extends FormRequest
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
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'team_id' => 'required|exists:teams,id',
            'format' => 'required|in:singles,doubles',
        ];
    }

    public function messages(): array
    {
        return [
            'team_id.required' => 'Team is required',
            'team_id.exists' => 'Selected team does not exist',
            'format.required' => 'Battle format is required',
            'format.in' => 'Format must be singles or doubles',
        ];
    }
}
