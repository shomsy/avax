<?php

declare(strict_types=1);

namespace Avax\HTTP\Request\tests\Unit;

use Avax\HTTP\Request\Request;
use Closure;
use LogicException;
use PHPUnit\Framework\TestCase;

class RequestDetachedDtoTest extends TestCase
{
    /**
     * @dataProvider detachedRequestAccessors
     */
    public function test_detached_request_accessors_throw_clear_exception(Closure $accessor): void
    {
        $request = $this->createDetachedRequest();

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Request is detached from ServerRequest');

        $accessor($request);
    }

    /**
     * @return iterable<string, array{0: Closure(Request): mixed}>
     */
    public static function detachedRequestAccessors(): iterable
    {
        yield 'serverRequest' => [static fn (Request $request) => $request->serverRequest()];
        yield 'method' => [static fn (Request $request) => $request->method()];
        yield 'uri' => [static fn (Request $request) => $request->uri()];
        yield 'header' => [static fn (Request $request) => $request->header('X-Test')];
        yield 'clientAddress' => [static fn (Request $request) => $request->clientAddress()];
    }

    private function createDetachedRequest(): Request
    {
        return new class([]) extends Request {};
    }
}
