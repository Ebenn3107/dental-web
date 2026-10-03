<?php

namespace App\Http\Requests;

use App\Support\Phone;
use Illuminate\Foundation\Http\FormRequest;

class CheckStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'code' => strtoupper(trim((string) $this->input('code'))),
            'phone' => trim((string) $this->input('phone')),
        ]);
    }

    public function rules(): array
    {
        return [
            // Kode boleh kosong: tanpa kode, pencarian jatuh ke nomor HP saja
            'code' => ['nullable', 'string', 'max:20'],
            'phone' => ['required', 'regex:'.Phone::VALIDATION_PATTERN],
        ];
    }

    public function messages(): array
    {
        return [
            'code.required' => 'Kode booking wajib diisi.',
            'phone.required' => 'Nomor HP wajib diisi.',
            'phone.regex' => 'Nomor HP harus format Indonesia, contoh: 081234567890',
        ];
    }
}
