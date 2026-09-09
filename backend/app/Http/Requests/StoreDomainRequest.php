<?php

namespace App\Http\Requests;

use App\Models\Domain;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDomainRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', [Domain::class, $this->route('site')]);
    }

    protected function prepareForValidation(): void
    {
        $host = strtolower(trim((string) $this->input('host')));
        // People paste what's in their address bar, so accept a full URL and
        // reduce it to the host.
        $host = preg_replace('#^https?://#', '', $host);
        $host = explode('/', $host)[0];

        $this->merge(['host' => $host]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'host' => [
                'required',
                'string',
                'max:253',
                // Dotted hostname, no leading/trailing hyphen in any label.
                'regex:/^(?!-)[a-z0-9-]{1,63}(?<!-)(\.(?!-)[a-z0-9-]{1,63}(?<!-))+$/',
                Rule::notIn([$this->appHost()]),
                Rule::unique('domains', 'host'),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'host.regex' => 'Введіть домен, напр. go.example.com — без протоколу й шляху.',
            'host.not_in' => 'Це власний домен застосунку, його не можна підключити.',
            'host.unique' => 'Цей домен уже підключено.',
        ];
    }

    /** Guards against someone claiming the app's own hostname. */
    private function appHost(): string
    {
        return strtolower((string) parse_url((string) config('app.url'), PHP_URL_HOST));
    }
}
