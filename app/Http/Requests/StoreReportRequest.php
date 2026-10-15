<?php

namespace App\Http\Requests;

use App\Models\IncidentType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreReportRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'village_id' => ['required', 'exists:villages,id'],
            'report_category_id' => ['required', 'exists:report_categories,id'],
            'incident_type_id' => ['nullable', 'exists:incident_types,id'],
            'reporter_name' => ['required', 'string', 'max:255'],
            'reporter_phone' => ['nullable', 'string', 'min:7', 'max:20'],
            'reported_at' => ['required', 'date'],
            'occurred_at' => ['nullable', 'date'],
            'location_name' => ['required', 'string', 'max:255'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'description' => ['required', 'string'],
            'risk_level' => ['required', 'in:low,medium,high'],
            'attachments' => ['nullable', 'array', 'max:5'],
            'attachments.*' => ['file', 'mimes:jpg,jpeg,png,pdf', 'max:20480'],
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            if (! $this->filled('incident_type_id') || ! IncidentType::query()->whereKey($this->integer('incident_type_id'))->where('report_category_id', $this->integer('report_category_id'))->exists()) {
                $validator->errors()->add('incident_type_id', 'Jenis kejadian tidak sesuai kategori.');
            }

            $villageId = $this->user()?->village_id;

            if ($villageId !== null && $this->integer('village_id') !== $villageId) {
                $validator->errors()->add('village_id', 'Anda hanya dapat membuat laporan untuk kampung Anda sendiri.');
            }
        }];
    }
}
