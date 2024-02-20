<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateUserRequest extends FormRequest
{

    public function rules(): array
    {
        return [
            'first_name' => ['string', 'max:20'],
            'last_name' => ['string', 'max:20'],
            'roles' => ['string'],
            'email' => ['string', 'max:30'],
            'password' => ['string'],
            'patronymic' => ['string', 'nullable'],
            'phone' => ['string', 'max:20'],
            'salary' => ['numeric'],
            'avatar' => ['nullable', 'file', 'mimes:png,jpg,jpeg']
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'error' => [
                'code' => 422,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ]
        ], 422)
        );
    }
}
