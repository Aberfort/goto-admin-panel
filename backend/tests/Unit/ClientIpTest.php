<?php

namespace Tests\Unit;

use App\Support\ClientIp;
use PHPUnit\Framework\TestCase;

class ClientIpTest extends TestCase
{
    private ClientIp $clientIp;

    protected function setUp(): void
    {
        parent::setUp();
        $this->clientIp = new ClientIp('test-secret');
    }

    public function test_same_ip_hashes_to_the_same_value(): void
    {
        $a = $this->clientIp->truncateAndHash('203.0.113.42');
        $b = $this->clientIp->truncateAndHash('203.0.113.42');

        $this->assertSame($a, $b);
    }

    public function test_ips_in_the_same_slash_24_hash_to_the_same_value(): void
    {
        $a = $this->clientIp->truncateAndHash('203.0.113.1');
        $b = $this->clientIp->truncateAndHash('203.0.113.254');

        $this->assertSame($a, $b);
    }

    public function test_ips_in_different_slash_24s_hash_differently(): void
    {
        $a = $this->clientIp->truncateAndHash('203.0.113.1');
        $b = $this->clientIp->truncateAndHash('203.0.114.1');

        $this->assertNotSame($a, $b);
    }

    public function test_never_returns_the_raw_ip(): void
    {
        $hash = $this->clientIp->truncateAndHash('203.0.113.42');

        $this->assertNotEquals('203.0.113.42', $hash);
        $this->assertStringNotContainsString('203.0.113', $hash);
    }

    public function test_null_ip_returns_null(): void
    {
        $this->assertNull($this->clientIp->truncateAndHash(null));
    }

    public function test_different_secrets_hash_the_same_ip_differently(): void
    {
        $a = (new ClientIp('secret-one'))->truncateAndHash('203.0.113.42');
        $b = (new ClientIp('secret-two'))->truncateAndHash('203.0.113.42');

        $this->assertNotSame($a, $b);
    }
}
