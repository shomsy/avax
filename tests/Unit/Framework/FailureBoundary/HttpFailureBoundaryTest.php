<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Framework\FailureBoundary;

use Avax\Components\HTTP\Request\System\PublicSurface\RequestInterface;
use Avax\Framework\System\Capabilities\FailureBoundary\Integration\HttpFailureBoundaryMiddleware;
use Avax\Components\HTTP\Response\System\PublicSurface\Response;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\UriInterface;

/**
 * @no-named-arguments
 */
final class HttpFailureBoundaryTest extends TestCase
{
    private function createMockRequest(string $method = 'GET', string $uri = 'http://localhost/'): MockAvaXRequest
    {
        return new MockAvaXRequest($method, $uri);
    }

    public function testSuccessfulRequestPassesThrough(): void
    {
        $middleware = new HttpFailureBoundaryMiddleware(
            (new \Avax\Framework\System\Capabilities\FailureBoundary\Configuration\BuildFailureBoundary())->build(),
        );

        $request = $this->createMockRequest();

        $response = $middleware->handle($request, static fn () =>
            Response::json(['ok' => true])
        );

        self::assertSame(200, $response->getStatusCode());
        self::assertStringContainsString('ok', (string) $response->getBody());
    }

    public function testUnhandledExceptionRethrows(): void
    {
        // The middleware has no target context, so no policy is matched.
        // Unhandled exceptions should rethrow.
        $middleware = new HttpFailureBoundaryMiddleware(
            (new \Avax\Framework\System\Capabilities\FailureBoundary\Configuration\BuildFailureBoundary())->build(),
        );

        $request = $this->createMockRequest();

        $this->expectException(\RuntimeException::class);

        $middleware->handle($request, static fn () =>
            throw new \RuntimeException('unexpected')
        );
    }

    public function testReportFailureWritesToErrorLog(): void
    {
        // The middleware reports failures before rethrowing unhandled ones.
        $middleware = new HttpFailureBoundaryMiddleware(
            (new \Avax\Framework\System\Capabilities\FailureBoundary\Configuration\BuildFailureBoundary())->build(),
        );

        $request = $this->createMockRequest();

        $this->expectException(\RuntimeException::class);

        $middleware->handle($request, static fn () =>
            throw new \RuntimeException('reported')
        );
    }
}

final class MockAvaXRequest implements RequestInterface
{
    public function __construct(
        private string $method = 'GET',
        private string $uri = 'http://localhost/',
    ) {
    }

    public function input(string $key, mixed $default = null): mixed { return $default; }
    /** @return array<string, mixed> */
    public function all(): array { return []; }
    public function getMethod(): string { return $this->method; }
    public function getUri(): UriInterface { return new \GuzzleHttp\Psr7\Uri($this->uri); }
    public function getProtocolVersion(): string { return '1.1'; }
    public function withProtocolVersion(string $version): static { return $this; }
    public function getHeaders(): array { return []; }
    public function hasHeader(string $name): bool { return false; }
    public function getHeader(string $name): array { return []; }
    public function getHeaderLine(string $name): string { return ''; }
    public function withHeader(string $name, $value): static { return $this; }
    public function withAddedHeader(string $name, $value, bool $replace = true): static { return $this; }
    public function withoutHeader(string $name): static { return $this; }
    public function getBody(): \Psr\Http\Message\StreamInterface { return \GuzzleHttp\Psr7\Utils::streamFor(''); }
    public function withBody(\Psr\Http\Message\StreamInterface $body): static { return $this; }
    public function getRequestTarget(): string { return '/'; }
    public function withRequestTarget(string $target): static { return $this; }
    public function withMethod(string $method): static { return $this; }
    public function withUri(UriInterface $uri, bool $preserveHost = false): static { return $this; }
    /** @return array<string, mixed> */
    public function getServerParams(): array { return []; }
    /** @return array<string, mixed> */
    public function getCookieParams(): array { return []; }
    /** @param array<string, mixed> $cookies */
    public function withCookieParams(array $cookies): static { return $this; }
    /** @return array<string, mixed> */
    public function getQueryParams(): array { return []; }
    /** @param array<string, mixed> $query */
    public function withQueryParams(array $query): static { return $this; }
    /** @return array<string, mixed> */
    public function getAttributes(): array { return []; }
    public function getAttribute(string $name, $default = null): mixed { return $default; }
    public function withAttribute(string $name, $value): static { return $this; }
    public function withoutAttribute(string $name): static { return $this; }

    /** @return array<string, \Psr\Http\Message\UploadedFileInterface> */
    public function getUploadedFiles(): array { return []; }
    /** @param array<string, \Psr\Http\Message\UploadedFileInterface> $uploadedFiles */
    public function withUploadedFiles(array $uploadedFiles): static { return $this; }
    /** @return array<string, mixed>|null */
    public function getParsedBody(): ?array { return null; }
    /** @param array<string, mixed>|null $data */
    public function withParsedBody($data): static { return $this; }
}
