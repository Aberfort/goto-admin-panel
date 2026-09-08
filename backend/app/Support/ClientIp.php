<?php

namespace App\Support;

/**
 * Privacy-by-default IP handling for click analytics: never store a raw
 * visitor IP. Truncates to a /24 (IPv4) or /64 (IPv6) network prefix, then
 * HMACs it with APP_KEY so the stored value isn't reversible to even that
 * truncated address - good enough for rough "is this the same visitor
 * again" analytics without keeping anything resembling PII at rest.
 */
class ClientIp
{
    public function truncateAndHash(?string $ip): ?string
    {
        if (empty($ip)) {
            return null;
        }

        $truncated = $this->truncate($ip);

        if ($truncated === null) {
            return null;
        }

        return hash_hmac('sha256', $truncated, (string) config('app.key'));
    }

    private function truncate(string $ip): ?string
    {
        $packed = inet_pton($ip);

        if ($packed === false) {
            return null;
        }

        if (strlen($packed) === 4) {
            // IPv4: zero the last octet -> /24
            return inet_ntop(substr($packed, 0, 3)."\0");
        }

        // IPv6: keep the first 8 bytes (/64), zero the rest.
        return inet_ntop(substr($packed, 0, 8).str_repeat("\0", 8));
    }
}
