<?php

declare(strict_types=1);

namespace Avax\HTTP\Request\Tests\Unit\IncomingHttp\ReadRequest\RequestTarget;

use Avax\HTTP\Request\IncomingHttp\IncomingRequest\RequestTarget\ReadRequestTarget;
use Avax\HTTP\URI\UriBuilder;
use PHPUnit\Framework\TestCase;

class ReadRequestTargetTest extends TestCase
{
    public function test_it_returns_slash_if_no_uri_and_no_target_set() : void
    {
        $targetOwner = new ReadRequestTarget(
            explicitTarget: null,
            uri           : new UriBuilder('')
        );

        $this->assertSame(expected: '/', actual: $targetOwner->resolve());
    }

    public function test_it_prioritizes_explicit_target_if_set() : void
    {
        $targetOwner = new ReadRequestTarget(
            explicitTarget: '*',
            uri           : new UriBuilder('http://example.com/api')
        );

        $this->assertSame(expected: '*', actual: $targetOwner->resolve());
    }

    public function test_it_builds_target_from_uri_if_no_explicit_target() : void
    {
        $targetOwner = new ReadRequestTarget(
            explicitTarget: null,
            uri           : new UriBuilder('http://example.com/api/users?status=active')
        );

        $this->assertSame(expected: '/api/users?status=active', actual: $targetOwner->resolve());
    }

    public function test_it_handles_uri_without_path() : void
    {
        $targetOwner = new ReadRequestTarget(
            explicitTarget: null,
            uri           : new UriBuilder('http://example.com')
        );

        $this->assertSame(expected: '/', actual: $targetOwner->resolve());
    }
}
