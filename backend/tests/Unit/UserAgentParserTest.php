<?php

namespace Tests\Unit;

use App\Support\UserAgentParser;
use PHPUnit\Framework\TestCase;

class UserAgentParserTest extends TestCase
{
    private UserAgentParser $parser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->parser = new UserAgentParser;
    }

    public function test_parses_a_desktop_chrome_user_agent(): void
    {
        $result = $this->parser->parse(
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/129.0.0.0 Safari/537.36'
        );

        $this->assertEquals('Chrome', $result['browser']);
        $this->assertEquals('Windows', $result['platform']);
        $this->assertEquals('desktop', $result['device_type']);
    }

    public function test_parses_a_mobile_safari_user_agent(): void
    {
        $result = $this->parser->parse(
            'Mozilla/5.0 (iPhone; CPU iPhone OS 17_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.6 Mobile/15E148 Safari/604.1'
        );

        $this->assertEquals('Mobile Safari', $result['browser']);
        $this->assertEquals('mobile', $result['device_type']);
    }

    public function test_returns_all_nulls_for_empty_user_agent(): void
    {
        $result = $this->parser->parse(null);

        $this->assertNull($result['browser']);
        $this->assertNull($result['platform']);
        $this->assertNull($result['device_type']);
    }

    public function test_returns_desktop_type_for_unrecognized_user_agent(): void
    {
        $result = $this->parser->parse('some-completely-unknown-client/1.0');

        $this->assertEquals('desktop', $result['device_type']);
    }
}
