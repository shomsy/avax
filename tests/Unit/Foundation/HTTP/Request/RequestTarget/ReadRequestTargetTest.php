<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Foundation\HTTP\Request\RequestTarget;

use Avax\HTTP\Request\RequestTarget\ReadRequestTarget;
use Avax\HTTP\URI\Uri;
use PHPUnit\Framework\TestCase;

/**
 * TDD Tests for ReadRequestTarget according to the DeepRefactor plan.
 */
class ReadRequestTargetTest extends TestCase
{
    public function test_reads_request_target_from_uri_path_only() : void
    {
        $uri    = new Uri('http://example.com/api/users');
        $target = ReadRequestTarget::read($uri);

        $this->assertSame(expected: '/api/users', actual: $target);
    }

    public function test_reads_request_target_from_uri_path_and_query() : void
    {
        $uri    = new Uri('http://example.com/api/users?status=active');
        $target = ReadRequestTarget::read($uri);

        $this->assertSame(expected: '/api/users?status=active', actual: $target);
    }

    public function test_keeps_zero_query_value_when_present_if_policy_requires() : void
    {
        $uri    = new Uri('http://example.com/api/users?0');
        $target = ReadRequestTarget::read($uri);

        $this->assertSame(expected: '/api/users?0', actual: $target);
    }

    public function test_does_not_confuse_request_target_with_uri_replacement() : void
    {
        $uri    = new Uri('http://example.com');
        $target = ReadRequestTarget::read($uri);

        // Root path is normally '/' in PSR-7 when no path is provided for an HTTP request
        $this->assertSame(expected: '/', actual: $target);
    }

    public function test_custom_request_target_takes_precedence_over_uri() : void
    {
        $uri    = new Uri('http://example.com/api/users');
        $target = ReadRequestTarget::read($uri, '/custom-target');

        $this->assertSame(expected: '/custom-target', actual: $target);
    }
}
