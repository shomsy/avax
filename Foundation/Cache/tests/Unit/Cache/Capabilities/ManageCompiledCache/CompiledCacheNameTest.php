<?php

declare(strict_types=1);

namespace Avax\Cache\Tests\Unit\Cache\Capabilities\ManageCompiledCache;

use Avax\Cache\System\Capabilities\ManageCompiledCache\CompiledCacheName;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class CompiledCacheNameTest extends TestCase
{
    public function test_it_accepts_simple_name_when_name_is_valid() : void
    {
        $name = new CompiledCacheName('routes');

        $this->assertSame('routes', $name->toString());
    }

    public function test_it_accepts_dotted_name_when_name_is_valid() : void
    {
        $name = new CompiledCacheName('routes.web');

        $this->assertSame('routes.web', $name->toString());
    }

    public function test_it_accepts_underscore_and_dash() : void
    {
        $name = new CompiledCacheName('routes_api-v2');

        $this->assertSame('routes_api-v2', $name->toString());
    }

    public function test_it_rejects_empty_name() : void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('cannot be empty');

        new CompiledCacheName('');
    }

    public function test_it_rejects_name_with_forward_slash() : void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('cannot contain');

        new CompiledCacheName('routes/test');
    }

    public function test_it_rejects_name_with_backslash() : void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('cannot contain');

        new CompiledCacheName('routes\\test');
    }

    public function test_it_rejects_name_with_parent_directory() : void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('cannot contain');

        new CompiledCacheName('routes/../evil');
    }

    public function test_it_rejects_name_that_is_too_long() : void
    {
        $longName = str_repeat('a', 129);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('must not exceed');

        new CompiledCacheName($longName);
    }

    public function test_it_rejects_invalid_characters() : void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('invalid characters');

        new CompiledCacheName('routes test');
    }
}