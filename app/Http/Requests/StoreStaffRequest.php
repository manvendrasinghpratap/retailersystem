<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Helpers\Settings;
class StoreStaffRequest extends FormRequest
{
    /**
     * Authorize request
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Validation rules
     */
    public function rules(): array
    {
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

            'email' => 'required|email|max:255|unique:users,email',

            'username' => 'required|max:25|unique:users,username',

            'cell_phone' => 'required|digits_between:10,11|unique:user_details,cell_phone',

            'password' => 'required|min:6|confirmed',

            'avatar' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ];
    }

    /**
     * Custom messages
     */
    public function messages(): array
    {
        return [
            'first_name.required' => 'First name is required',
            'last_name.required' => 'Last name is required',
            'email.unique' => 'Email already exists',
            'username.unique' => 'Username already exists',
            'mobile_no.unique' => 'Mobile number already exists',
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