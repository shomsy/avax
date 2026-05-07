<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Application\Config\Capabilities\Architecture;

use Avax\Components\Application\Config\System\Capabilities\Architecture\AppPath;
use PHPUnit\Framework\TestCase;

final class AppPathTest extends TestCase
{
    public function test_get_root_returns_directory_with_separator() : void
    {
        $root = AppPath::getRoot();

        $this->assertStringEndsWith(suffix: DIRECTORY_SEPARATOR, string: $root);
        $this->assertFileExists(filename: $root . 'composer.json');
    }

    public function test_get_returns_full_path() : void
    {
        $path = AppPath::AUTOLOAD_PATH->get();

        $this->assertStringContainsString(needle: 'vendor/autoload.php', haystack: $path);
    }
}
