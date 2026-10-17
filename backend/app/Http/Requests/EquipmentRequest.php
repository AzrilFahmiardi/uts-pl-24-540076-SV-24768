<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EquipmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $equipment = $this->route('equipment');

        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', Rule::unique('equipment', 'code')->ignore($equipment)],
            'category' => ['required', 'string', 'max:100'],
            'condition' => ['required', Rule::in(['baik', 'rusak ringan', 'rusak berat'])],
            'quantity' => ['required', 'integer', 'min:0'],
        ];
    }
}
