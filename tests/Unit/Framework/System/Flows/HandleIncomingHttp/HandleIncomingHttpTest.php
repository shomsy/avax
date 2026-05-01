<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Framework\System\Flows\HandleIncomingHttp;

use Avax\Framework\System\Capabilities\Runtime\RuntimeRequest;
use Avax\Framework\System\Configuration\BuildApplication\BuildApplication;
use Avax\Framework\System\PublicSurface\Avax;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class HandleIncomingHttpTest extends TestCase
{
    public function test_it_uses_the_existing_response_component_to_normalize_array_payloads(): void
    {
        $avax = Avax::boot(
            builder: BuildApplication::fromProjectPath(projectPath: $this->projectRoot())
                ->withHttpHandler(
                    httpHandler: static fn (): array => ['status' => 'ok'],
                ),
        );

        $runtimeResponse = $avax->http()->handle(
            request: new RuntimeRequest(method: 'GET', uri: '/status'),
        );

        self::assertSame(200, $runtimeResponse->statusCode());
        self::assertSame(['application/json'], $runtimeResponse->headers()['Content-Type']);
        self::assertStringContainsString('"status":"ok"', $runtimeResponse->body());
        self::assertFalse($avax->requestScopes()->hasCurrent());
    }

    public function test_it_returns_a_safe_error_response_when_the_handler_throws(): void
    {
        $avax = Avax::boot(
            builder: BuildApplication::fromProjectPath(projectPath: $this->projectRoot())
                ->withHttpHandler(
                    httpHandler: static function (): never {
                        throw new RuntimeException(message: 'boom');
                    },
                ),
        );

        $runtimeResponse = $avax->http()->handle(
            request: new RuntimeRequest(method: 'GET', uri: '/explode'),
        );

        self::assertSame(500, $runtimeResponse->statusCode());
        self::assertStringContainsString('Internal Server Error', $runtimeResponse->body());
    }

    private function projectRoot(): string
    {
        return dirname(path: __DIR__, levels: 6);
    }
}
