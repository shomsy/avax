<?php

declare(strict_types=1);

namespace Avax\Tests\Foundation\HTTP\URI;

use Avax\HTTP\URI\UriBuilder;
use Avax\Tests\TestCase;
use PHPUnit\Framework\TestCase;

final class UriBuilderTest extends TestCase
{
    public function test_can_build_relative_uri_without_scheme_and_host() : void
    {
        $uri = new UriBuilder(path: '/health');

        self::assertSame('/health', $uri->build());
    }

    public function test_appends_path_and_query_parameters() : void
    {
        $uri = UriBuilder::createFromString('https://example.com/api')
            ->appendPath('users?page=2');

        self::assertSame('https://example.com/api/users?page=2', $uri->build());
    }
}
