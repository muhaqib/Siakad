<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class LaporanAbsensiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isSuperAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'periode' => ['sometimes', 'required', 'date_format:Y-m'],
            'jenis' => ['sometimes', 'required', 'in:dosen,mahasiswa'],
            'semester' => ['exclude_unless:jenis,mahasiswa', 'nullable', 'integer', 'between:1,14'],
            'prodi_id' => ['exclude_unless:jenis,mahasiswa', 'nullable', 'integer', 'exists:prodi,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'semester.integer' => 'Semester harus berupa angka.',
            'semester.between' => 'Semester harus antara 1 dan 14.',
            'prodi_id.integer' => 'Program studi tidak valid.',
            'prodi_id.exists' => 'Program studi tidak ditemukan.',
            'jenis.required' => 'Pilih jenis laporan.',
            'jenis.in' => 'Jenis laporan harus dosen atau mahasiswa.',
            'periode.required' => 'Pilih bulan laporan.',
            'periode.date_format' => 'Periode harus berupa bulan dan tahun yang valid (YYYY-MM).',
        ];
    }
}
