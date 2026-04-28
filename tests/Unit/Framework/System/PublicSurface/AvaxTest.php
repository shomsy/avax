<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Framework\System\PublicSurface;

use Avax\Framework\System\Capabilities\Runtime\RuntimeRequest;
use Avax\Framework\System\Configuration\BuildApplication\BuildApplication;
use Avax\Framework\System\PublicSurface\Avax;
use PHPUnit\Framework\TestCase;

final class AvaxTest extends TestCase
{
    public function test_it_boots_a_small_public_surface_that_delegates_to_framework_flows(): void
    {
        $application = Avax::boot(
            builder: BuildApplication::fromProjectPath(projectPath: $this->projectRoot())
                ->withHttpHandler(httpHandler: static fn (): string => 'pong')
                ->registerConsoleCommand(name: 'ping', command: static fn (): string => 'pong'),
        );

        $response = $application->http()->handle(
            request: new RuntimeRequest(method: 'GET', uri: '/health'),
        );

        self::assertTrue($application->state()->isBooted());
        self::assertSame('pong', $response->body());
        self::assertSame('pong', $application->console()->run(commandName: 'ping')->output());
    }

    private function projectRoot(): string
    {
        return dirname(path: __DIR__, levels: 5);
    }
}
