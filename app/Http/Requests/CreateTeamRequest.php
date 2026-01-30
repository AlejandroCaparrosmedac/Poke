<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateTeamRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'pokemon_data' => 'required|array|min:1|max:6',
            'pokemon_data.*.name' => 'required|string',
            'pokemon_data.*.level' => 'required|integer|min:1|max:100',
            'pokemon_data.*.moves' => 'array|max:4',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Team name is required',
            'pokemon_data.required' => 'At least one Pokémon is required',
            'pokemon_data.max' => 'Maximum 6 Pokémon per team',
        ];
    }
}
