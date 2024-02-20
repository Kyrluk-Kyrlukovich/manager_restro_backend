<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateRoleRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            "code" => "required|unique:roles,code",
            "role" => "required"
        ];
    }
}
