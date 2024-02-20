<?php

namespace App\Http\Requests;

use App\Models\Table;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rules\Enum;

class UpdateTableRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function rules(): array
    {
        return [
            'number' => ['numeric'],
            'status' => ['in:emty,reserved,taken'],
            'placements' => ['numeric'],
            'served' => ['numeric', 'exists:users,id', 'nullable'],
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
