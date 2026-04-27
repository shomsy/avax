<?php

declare(strict_types=1);

namespace components\Tests\Unit\Foundation\HTTP\Request\RequestTarget;

use components\HTTP\Request\ServerRequest\IncomingRequest\RequestTarget\ReadRequestTarget;
use components\HTTP\URI\UriBuilder;
use components\Tests\TestCase;

/**
 * TDD Tests for ReadRequestTarget according to the DeepRefactor plan.
 */
class ReadRequestTargetTest extends TestCase
{
    public function test_reads_request_target_from_uri_path_only() : void
    {
        $uri    = UriBuilder::createFromString(uri: 'https://example.com/api/users');
        $target = new ReadRequestTarget(explicitTarget: null, uri: $uri)->resolve();

        $this->assertSame(expected: '/api/users', actual: $target);
    }

    public function test_reads_request_target_from_uri_path_and_query() : void
    {
        $uri    = UriBuilder::createFromString(uri: 'https://example.com/api/users?status=active');
        $target = new ReadRequestTarget(explicitTarget: null, uri: $uri)->resolve();

        $this->assertSame(expected: '/api/users?status=active', actual: $target);
    }

    public function test_keeps_zero_query_value_when_present_if_policy_requires() : void
    {
        $uri    = UriBuilder::createFromString(uri: 'https://example.com/api/users?status=0');
        $target = new ReadRequestTarget(explicitTarget: null, uri: $uri)->resolve();

        $this->assertSame(expected: '/api/users?status=0', actual: $target);
    }

    public function test_does_not_confuse_request_target_with_uri_replacement() : void
    {
        $uri    = UriBuilder::createFromString(uri: 'https://example.com');
        $target = new ReadRequestTarget(explicitTarget: null, uri: $uri)->resolve();

        // Root path is normally '/' in PSR-7 when no path is provided for an HTTP request
        $this->assertSame(expected: '/', actual: $target);
    }

    public function test_custom_request_target_takes_precedence_over_uri() : void
    {
        $uri    = UriBuilder::createFromString(uri: 'https://example.com/api/users');
        $target = new ReadRequestTarget(explicitTarget: '/custom-target', uri: $uri)->resolve();

        $this->assertSame(expected: '/custom-target', actual: $target);
    }
}
