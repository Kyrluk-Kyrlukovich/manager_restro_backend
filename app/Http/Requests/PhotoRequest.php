<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class PhotoRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'image' => ['nullable', 'file', 'mimes:png,jpg,jpeg']
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
