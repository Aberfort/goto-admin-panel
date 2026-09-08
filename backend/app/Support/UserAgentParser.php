<?php

namespace App\Support;

use UAParser\Parser;
use UAParser\Result\Client;

/**
 * Thin wrapper around ua-parser/uap-php - local regex matching against a
 * bundled database, no external API calls, so click logging never depends
 * on a third-party service being up.
 */
class UserAgentParser
{
    public function parse(?string $userAgent): array
    {
        if (empty($userAgent)) {
            return $this->empty();
        }

        $client = Parser::create()->parse($userAgent);

        return [
            'browser' => $this->nullIfOther($client->ua->family),
            'browser_version' => $client->ua->major ? $client->ua->toVersion() : null,
            'platform' => $this->nullIfOther($client->os->family),
            'device_type' => $this->deviceType($client),
        ];
    }

    private function deviceType(Client $client): string
    {
        $deviceFamily = $client->device->family ?? 'Other';
        $os = $client->os->family ?? '';

        if (stripos($deviceFamily, 'iPad') !== false || stripos($deviceFamily, 'Tablet') !== false) {
            return 'tablet';
        }

        if ($deviceFamily !== 'Other' && $deviceFamily !== '') {
            return 'mobile';
        }

        // The device regexes don't catch every case - OS is a reasonable
        // fallback signal for phones that slipped through as "Other".
        if (in_array($os, ['iOS', 'Android'], true)) {
            return 'mobile';
        }

        return 'desktop';
    }

    private function nullIfOther(?string $value): ?string
    {
        return $value === 'Other' ? null : $value;
    }

    private function empty(): array
    {
        return [
            'browser' => null,
            'browser_version' => null,
            'platform' => null,
            'device_type' => null,
        ];
    }
}
