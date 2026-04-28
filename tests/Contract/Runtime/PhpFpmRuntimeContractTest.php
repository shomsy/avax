<?php

declare(strict_types=1);

namespace Avax\Tests\Contract\Runtime;

use Avax\Framework\System\Capabilities\Runtime\PhpFpm\PhpFpmRuntime;
use Avax\Framework\System\Configuration\BuildApplication\BuildApplication;
use Avax\Framework\System\PublicSurface\Avax;
use PHPUnit\Framework\TestCase;

final class PhpFpmRuntimeContractTest extends TestCase
{
    public function test_it_handles_one_php_fpm_style_request_without_leaking_scope(): void
    {
        $application = Avax::boot(
            builder: BuildApplication::fromProjectPath(projectPath: $this->projectRoot())
                ->withHttpHandler(
                    httpHandler: static function ($request, $runtime): string {
                        $runtime->requestScopes()->current()->write(key: 'uri', value: $request->uri());

                        return 'handled ' . $runtime->requestScopes()->current()->read(key: 'uri');
                    },
                ),
        );

        $runtime  = new PhpFpmRuntime(httpKernel: $application->http());
        $response = $runtime->handleGlobals(
            server: [
                'REQUEST_METHOD' => 'GET',
                'REQUEST_URI'    => '/php-fpm',
            ],
        );

        self::assertSame(200, $response->statusCode());
        self::assertSame('handled /php-fpm', $response->body());
        self::assertFalse($application->requestScopes()->hasCurrent());
    }

    private function projectRoot(): string
    {
        return dirname(__DIR__, 3);
    }
}
