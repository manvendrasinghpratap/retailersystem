<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Helpers\Settings;

class UpdateStaffRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = Settings::getDecodeCode($this->user_id);
        return [

            'first_name' => 'required|string|max:25',
            'last_name' => 'required|string|max:25',

            'street_address' => 'required|string',
            'local_government' => 'required|string',
            'country_of_origin' => 'required|string',
            'state_of_origin' => 'required|string',

            'date_of_birth' => 'required|date',
            'nin' => 'required|string',

            'emergency_contact_name' => 'required|string',
            'emergency_phone' => 'required|string',
            'emergency_relationship' => 'required|string',

            'staff_suffix' => 'required|string',
            'emergency_suffix' => 'required|string',
            'guarantor_suffix' => 'required|string',

            'guarantor_name' => 'required|string',
            'guarantor_address' => 'required|string',
            'guarantor_phone' => 'required|string',

            'designation_id' => 'required|integer',

            'hire_date' => 'required|date',

            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users','email')->ignore($userId)
            ],

            // 'username' => [
            //     'required',
            //     'max:25',
            //     Rule::unique('users','username')->ignore($userId)
            // ],

            'cell_phone' => [
                'required',
                'digits_between:10,11',
                Rule::unique('user_details','cell_phone')->ignore($userId,'user_id')
            ],

            'avatar' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'hire_date' => Settings::formatDate($this->hire_date, 'Y-m-d'),
            'date_of_birth' => Settings::formatDate($this->date_of_birth, 'Y-m-d'),
        ]);
    }
}