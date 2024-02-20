<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class CreateShiftRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => 'required',
            'date_start' => 'required|date|after_or_equal:today|before:date_end|unique:shifts,date_start',
            'date_end' => 'required|date|after:date_start|unique:shifts,date_end',
            'count_staff' => 'required|numeric'

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
