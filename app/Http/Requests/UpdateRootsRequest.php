<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRootsRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            '*' => "boolean"
        ];
    }
}
