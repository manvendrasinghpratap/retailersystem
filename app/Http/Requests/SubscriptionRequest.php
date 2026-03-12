<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SubscriptionRequest extends FormRequest
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
			'name'     => ['required', 'string', 'max:100'],
			'price'    => ['required', 'numeric', 'min:0'],
			'duration' => ['required', 'integer', 'min:1', 'max:36'],
			'description' => ['nullable', 'string', 'max:500'],
		];
    }
}