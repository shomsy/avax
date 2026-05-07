<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\HTTP\Client;

use Avax\Components\HTTP\Client\System\Capabilities\Testing\FakeHttpClient;
use Avax\Components\HTTP\Client\System\Capabilities\Testing\RecordedHttpResponse;
use PHPUnit\Framework\TestCase;

final class HttpClientCapabilitiesTest extends TestCase
{
    public function test_fake_http_client_returns_recorded_response() : void
    {
        $fake = FakeHttpClient::create()
            ->whenGet('https://api.example.com')
            ->respondWithJson(['ok' => true])
            ->build();

        $actual = $fake->get('https://api.example.com');

        $this->assertSame(200, $actual->statusCode);
        $this->assertSame('{"ok":true}', $actual->body);
    }
}
