<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ZiwoTokenRequest extends FormRequest
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
            'location_id' => 'nullable|string', // Ensure it exists in your table
            'is_expire' => 'nullable|boolean', // Optional flag for token expiration
        ];
    }
}
