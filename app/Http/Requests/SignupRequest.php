<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Foundation\Http\FormRequest;

class SignupRequest extends FormRequest
{

    public function rules(): array
    {
        return [
            'first_name' => ['required','string', 'max:20'],
            'last_name' => ['required','string', 'max:20'],
            'patronymic' => ['nullable','string', 'max:20'],
            'roles' => ['required', 'exists:roles,id'],
            'email' => ['required','string', 'max:30', 'unique:users,email'],
            'password' => ['required','string'],
            'phone' => ['required','string', 'max:20', 'unique:users,phone'],
            'salary' => ['required', 'numeric'],
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'error' => [
                'code' => 422,
                'message'=> 'Validation failed',
                'errors' => $validator->errors()
            ]
        ], 422)
        );
    }
}
