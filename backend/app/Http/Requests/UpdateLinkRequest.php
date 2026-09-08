<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLinkRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('link'));
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'target_url' => ['sometimes', 'required', 'url', 'max:2048'],
            'is_active' => ['sometimes', 'boolean'],
            // short_code is deliberately not editable here - once a short
            // link is shared, changing its code would break it. See the
            // dedicated toggle endpoint for is_active flips.
        ];
    }
}
