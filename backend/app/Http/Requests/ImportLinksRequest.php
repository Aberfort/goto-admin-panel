<?php

namespace App\Http\Requests;

use App\Models\Link;
use Illuminate\Foundation\Http\FormRequest;

class ImportLinksRequest extends FormRequest
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
            'file' => ['required', 'file', 'max:2048', 'mimetypes:text/plain,text/csv,application/csv'],
        ];
    }

    public function messages(): array
    {
        return [
            'file.mimetypes' => 'Файл має бути у форматі CSV.',
        ];
    }
}
