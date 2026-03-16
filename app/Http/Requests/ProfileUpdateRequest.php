<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProfileUpdateRequest extends FormRequest
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
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'first_name'                    => ['required', 'string', 'max:255'],
            'last_name'                     => ['required', 'string', 'max:255'],
            'office_phone'                  => ['required', 'string', 'max:20'],
            'cell_phone'                    => ['required', 'string', 'max:20'],
            'whatsapp_number'               => ['required', 'string', 'max:20'],
            'nin'                           => ['required', 'string', 'max:50'],
            'local_government'              => ['required', 'string', 'max:255'],
            'country_of_origin'             => ['required', 'string', 'max:255'],
            'state_of_origin'               => ['required', 'string', 'max:255'],
        ];
    }
}