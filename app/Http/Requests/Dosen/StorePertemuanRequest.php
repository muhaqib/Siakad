<?php

namespace App\Http\Requests\Dosen;

use Illuminate\Foundation\Http\FormRequest;

class StorePertemuanRequest extends FormRequest
{
    public function authorize(): bool
    {
        $dosen = $this->user()?->dosen;

        return $dosen && $this->route('kelas')->dosen_id === $dosen->id;
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'jadwal_kuliah_id' => ['required', 'exists:jadwal_kuliah,id'],
            'pertemuan_ke' => ['required', 'integer', 'min:1', 'max:16'],
            'tanggal' => ['required', 'date'],
            'materi' => ['required', 'string', 'max:255'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'materi.required' => 'Materi wajib diisi.',
            'materi.string' => 'Materi harus berupa teks.',
            'materi.max' => 'Materi maksimal 255 karakter.',
        ];
    }
}
