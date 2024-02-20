<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRoleRequest extends FormRequest
{

    public function rules(): array
    {
        return [
            "code" => "required",
            "role" => "required"
        ];
    }
}
