<?php

namespace App\Http\Requests;

use App\Models\TerrainReport;
use Illuminate\Foundation\Http\FormRequest;

class StoreTerrainReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', TerrainReport::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'date' => ['nullable', 'date'],
            'nb_ventes' => ['nullable', 'integer', 'min:0'],
            'photo' => ['nullable', 'image', 'max:4096'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.quantite' => ['required', 'integer', 'min:1'],
            'items.*.prix_unitaire' => ['required', 'numeric', 'min:0'],
        ];
    }
}
