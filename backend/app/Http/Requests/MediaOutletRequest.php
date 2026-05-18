<?php

namespace App\Http\Requests;

use App\Models\MediaOutlet;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MediaOutletRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isEditor() ?? false;
    }

    public function rules(): array
    {
        return [
            'city_id'      => ['required', 'exists:cities,id'],
            'name'         => ['required', 'string', 'max:255'],
            'url'          => ['required', 'url', 'max:512'],
            'type'         => ['required', Rule::in(MediaOutlet::TYPES)],
            'language'     => ['required', Rule::in(MediaOutlet::LANGUAGES)],
            'description'  => ['nullable', 'string', 'max:5000'],
            'logo_url'     => ['nullable', 'url', 'max:512'],
            'founded_year' => ['nullable', 'integer', 'min:1600', 'max:2100'],
            'is_active'    => ['boolean'],
            'is_featured'  => ['boolean'],
            'latitude'     => ['nullable', 'numeric', 'between:-90,90'],
            'longitude'    => ['nullable', 'numeric', 'between:-180,180'],
        ];
    }
}
