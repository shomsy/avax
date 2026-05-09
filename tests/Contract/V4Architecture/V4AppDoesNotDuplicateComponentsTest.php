<?php

declare(strict_types=1);

namespace Avax\Tests\Contract\V4Architecture;

use PHPUnit\Framework\TestCase;

/**
 * Proves V4-01 App layer does not bypass or duplicate existing components.
 */
final class V4AppDoesNotDuplicateComponentsTest extends TestCase
{
    private string $appCode;
    private string $runCode;
    private string $secureRequestCode;

    protected function setUp(): void
    {
        parent::setUp();
        $basePath = dirname(__DIR__, 3);
        $this->appCode = file_get_contents($basePath . '/framework/System/PublicSurface/App.php');
        $this->runCode = file_get_contents($basePath . '/framework/System/Flows/RunApplication/RunApplication.php');
        $this->secureRequestCode = file_get_contents($basePath . '/components/HTTP/SecureRequest/System/PublicSurface/SecureRequest.php');
    }

    public function testAppDoesNotImplementRouter(): void
    {
        // Must not contain router implementation
        self::assertStringNotContainsString('class Router', $this->appCode);
        self::assertStringNotContainsString('class Router', $this->runCode);

        // Must use existing RouteDefinition
        self::assertStringContainsString('RouteDefinition', $this->appCode);
        self::assertStringContainsString('RouteDefinition', $this->runCode);
    }

    public function testAppDoesNotImplementContainer(): void
    {
        // Must not contain container implementation
        self::assertStringNotContainsString('class Container', $this->appCode);

        // Uses existing RouteFacadeContainer
        self::assertStringContainsString('RouteFacadeContainer', $this->runCode);
    }

    public function testAppDoesNotDuplicateDataTransfer(): void
    {
        // Extract non-comment lines
        $appLines = array_filter(explode("\n", $this->appCode), fn ($line) => ! str_starts_with(trim($line), '*') && ! str_starts_with(trim($line), '//'));
        $runLines = array_filter(explode("\n", $this->runCode), fn ($line) => ! str_starts_with(trim($line), '*') && ! str_starts_with(trim($line), '//'));

        $appCodeOnly = implode("\n", $appLines);
        $runCodeOnly = implode("\n", $runLines);

        self::assertStringNotContainsString('DataTransfer', $appCodeOnly);
        self::assertStringNotContainsString('DataTransfer', $runCodeOnly);
        self::assertStringNotContainsString('hydrate', $appCodeOnly);
        self::assertStringNotContainsString('hydrate', $runCodeOnly);
    }

    public function testAppPublicSurfaceIsThin(): void
    {
        $lineCount = count(explode("\n", $this->appCode));
        self::assertLessThan(400, $lineCount, 'App PublicSurface should stay thin (under 400 lines)');
    }

    public function testRunApplicationUsesExistingMatchHttpRoute(): void
    {
        self::assertStringContainsString('MatchHttpRoute', $this->runCode);
        self::assertStringContainsString('ReadIncomingHttpRequest', $this->runCode);
    }

    public function testSecureRequestDelegatesToDataTransfer(): void
    {
        self::assertStringContainsString('DataTransfer', $this->secureRequestCode);
        self::assertStringContainsString('CreateDataObject', $this->secureRequestCode);
    }

    public function testNoReactPhpInAppPublicApi(): void
    {
        self::assertStringNotContainsString('React', $this->appCode);
        self::assertStringNotContainsString('reactphp', $this->appCode);
        self::assertStringNotContainsString('EventLoop', $this->appCode);
    }
}
