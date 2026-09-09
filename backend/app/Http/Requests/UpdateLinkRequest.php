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
            // No after:now here - an already-expired link should still be
            // editable without being forced to move its date forward.
            'expires_at' => ['sometimes', 'nullable', 'date'],
            // Send it empty to remove the password entirely.
            'password' => ['sometimes', 'nullable', 'string', 'min:4', 'max:255'],
            // short_code is deliberately not editable here - once a short
            // link is shared, changing its code would break it. See the
            // dedicated toggle endpoint for is_active flips.
        ];
    }
}
