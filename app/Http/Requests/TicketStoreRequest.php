<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TicketStoreRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'kategori' => ['required', Rule::in(['IT','PRODUKSI','GA','LAINNYA'])],
            'urgensi'  => ['required', Rule::in(['RENDAH','SEDANG','TINGGI','DARURAT'])],

            // aset terdaftar (opsional)
            'asset_id' => ['nullable', 'integer', 'exists:assets,id'],
            'is_asset_unlisted' => ['boolean'],

            // aset manual jika belum terdaftar / kategori LAINNYA
            'asset_nama_manual'   => ['nullable','string','max:120'],
            'asset_lokasi_manual' => ['nullable','string','max:120'],
            'asset_vendor_manual' => ['nullable','string','max:120'],

            'judul'     => ['required','string','max:150'],
            'deskripsi' => ['required','string'],

            // PJ (boleh kosong kecuali LAINNYA)
            'assignee_id' => ['nullable','integer','exists:users,id'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($v) {
            $data = $this->all();
            $kategori = $data['kategori'] ?? null;
            $unlisted = (bool)($data['is_asset_unlisted'] ?? false);
            $assetId  = $data['asset_id'] ?? null;

            // Validasi kombinasi aset
            if ($kategori !== 'LAINNYA' && !$unlisted && !$assetId) {
                $v->errors()->add('asset_id', 'Pilih aset atau centang "Aset belum terdaftar".');
            }
            if ($kategori === 'LAINNYA') {
                if (empty($data['assignee_id'])) {
                    $v->errors()->add('assignee_id', 'Untuk kategori LAINNYA, Penanggung Jawab wajib dipilih.');
                }
                if (empty($data['asset_nama_manual']) || empty($data['asset_lokasi_manual'])) {
                    $v->errors()->add('asset_nama_manual', 'Nama & lokasi aset wajib diisi untuk kategori LAINNYA.');
                }
            }
            if ($unlisted && (empty($data['asset_nama_manual']) || empty($data['asset_lokasi_manual']))) {
                $v->errors()->add('asset_nama_manual', 'Nama & lokasi aset wajib diisi jika aset belum terdaftar.');
            }
        });
    }
}
