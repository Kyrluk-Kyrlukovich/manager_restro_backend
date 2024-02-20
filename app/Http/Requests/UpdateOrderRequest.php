<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateOrderRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'status' => ['nullable', 'in:accept,prepare,ready,passed'],
            'table_id' => ['nullable', 'exists:tables,id'],
            'dishes' => ["nullable", "array"],
            'dishes.*.dish_id' => ['required', 'exists:dishes,id'],
            'dishes.*.count' => ['required', 'numeric'],
            'notes' => ['nullable', 'string'],
            'responsible' => ['nullable', 'exists:users,id'],
            'chef' => ['nullable', 'exists:users,id'],
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
