<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePaymentTypeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->isSuperAdmin() ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'category' => ['sometimes', 'required', 'string', 'in:registration,semester,other'],
            'semester' => ['nullable', 'integer', 'min:1', 'max:14', 'required_if:category,semester'],
            'default_amount' => ['required', 'numeric', 'min:0'],
            'is_active' => ['required', 'boolean'],
            'description' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'nama jenis pembayaran',
            'category' => 'kategori',
            'semester' => 'semester',
            'default_amount' => 'tarif default',
            'is_active' => 'status keaktifan',
            'description' => 'deskripsi / keterangan',
        ];
    }

    /**
     * Get custom validation messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Nama jenis pembayaran wajib diisi.',
            'category.required' => 'Kategori pembayaran wajib dipilih.',
            'category.in' => 'Kategori yang dipilih tidak valid.',
            'semester.required_if' => 'Semester wajib diisi jika kategori adalah semester.',
            'semester.min' => 'Semester minimal adalah 1.',
            'semester.max' => 'Semester maksimal adalah 14.',
            'default_amount.required' => 'Besaran tarif default wajib diisi.',
            'default_amount.numeric' => 'Besaran tarif default harus berupa angka nominal.',
            'default_amount.min' => 'Besaran tarif default tidak boleh bernilai negatif.',
            'is_active.required' => 'Status keaktifan wajib ditentukan.',
        ];
    }
}
