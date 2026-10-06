<?php

namespace App\Http\Requests\Dosen;

use App\Models\KehadiranDosen;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AbsenMasukRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->dosen !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in(array_keys(KehadiranDosen::getAbsenHarianStatusList()))],
            'keterangan' => ['nullable', 'required_unless:status,'.KehadiranDosen::STATUS_HADIR, 'string', 'max:1000'],
            'bukti_file' => ['nullable', 'prohibited_if:status,'.KehadiranDosen::STATUS_HADIR, 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'status.required' => 'Pilih status kehadiran terlebih dahulu.',
            'status.in' => 'Status kehadiran tidak valid.',
            'keterangan.required_unless' => 'Alasan wajib diisi jika Anda tidak hadir.',
            'keterangan.max' => 'Alasan maksimal 1000 karakter.',
            'bukti_file.prohibited_if' => 'Bukti hanya diperlukan untuk status sakit, izin, atau tugas luar.',
            'bukti_file.mimes' => 'Bukti harus berupa file JPG, PNG, atau PDF.',
            'bukti_file.max' => 'Ukuran bukti maksimal 2 MB.',
        ];
    }
}
