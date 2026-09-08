<?php

namespace App\Http\Requests;

use App\Models\Link;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLinkRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', [Link::class, $this->route('site')]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'target_url' => ['required', 'url', 'max:2048'],
            // Empty/omitted -> Link::boot() auto-generates one.
            'short_code' => ['nullable', 'string', 'max:64', 'alpha_dash', Rule::unique('links')],
        ];
    }
}
