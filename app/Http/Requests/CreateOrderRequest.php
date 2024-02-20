<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */

    public function rules(): array
    {
        return [
            'status' => ['required', 'in:accept,prepare,ready,passed'],
            'table_id' => ['required', 'exists:tables,id'],
            'notes' => ['nullable', 'string'],
            'dishes' => ["nullable", "array"],
            'dishes.*.dish_id' => ['required', 'exists:dishes,id'],
            'dishes.*.count' => ['required', 'numeric'],
            'responsible' => ['required', 'exists:users,id'],
            'chef' => ['nullable', 'exists:users,id'],
        ];
    }
}
