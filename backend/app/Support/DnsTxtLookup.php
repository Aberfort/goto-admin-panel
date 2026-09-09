<?php

namespace App\Support;

/**
 * Thin seam over dns_get_record so domain verification can be tested
 * without touching the network - tests bind a fake in the container.
 */
class DnsTxtLookup
{
    /**
     * Every TXT value published at $name, or an empty array when the name
     * doesn't resolve.
     *
     * @return array<int, string>
     */
    public function txtValues(string $name): array
    {
        // @ suppresses the PHP warning a non-existent name produces; the
        // false return is handled right below.
        $records = @dns_get_record($name, DNS_TXT);

        if ($records === false) {
            return [];
        }

        return array_values(array_filter(
            array_map(fn (array $record) => $record['txt'] ?? null, $records)
        ));
    }
}
