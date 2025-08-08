<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePengajuanPinjamanRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $rawNominal = (string) $this->input('nominal', '');
        $sanitizedNominal = (int) preg_replace('/[^\d]/', '', $rawNominal);

        $this->merge([
            'nominal' => $sanitizedNominal,
            'lama_angsuran' => (int) $this->input('lama_angsuran')
        ]);
    }

    public function authorize(): bool
    {
        return auth()->guard('member')->check();
    }

    public function rules(): array
    {
        return [
            // jenis_pinjaman opsional, default logic menggunakan 1 (Biasa)
            'jenis_pinjaman' => ['sometimes'],
            'nominal' => ['required','numeric','min:1000'],
            'lama_angsuran' => ['required','integer','min:1','max:60'],
            'keterangan' => ['required','string','max:500'],
        ];
    }
}


