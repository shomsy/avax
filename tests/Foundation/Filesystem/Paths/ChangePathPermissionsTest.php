<?php

declare(strict_types=1);

namespace Avax\Tests\Foundation\Filesystem\Paths;

use Avax\Tests\TestCase;
use components\Filesystem\Paths\ChangePathPermissions;
use RuntimeException;

class ChangePathPermissionsTest extends TestCase
{
    private string $testFile;

    protected function setUp(): void
    {
        parent::setUp();
        $this->testFile = '/home/shomsy/projects/components/tests/fixtures/Filesystem/perm_test.txt';
    }

    protected function tearDown(): void
    {
        @unlink(filename: $this->testFile);
        parent::tearDown();
    }

    public function test_execute_sets_permissions(): void
    {
        file_put_contents(filename: $this->testFile, data: "sadrzaj\n");

        $result = new ChangePathPermissions()->execute(path: $this->testFile, permissions: 0o644);

        self::assertTrue(condition: $result);
    }

    public function test_execute_throws_exception_for_non_existent_path(): void
    {
        $this->expectException(exception: RuntimeException::class);
        $this->expectExceptionMessage(message: 'Path does not exist:');

        new ChangePathPermissions()->execute(path: '/ne postoji put', permissions: 0o644);
    }

    public function test_execute_returns_false_for_failure(): void
    {
        if (posix_getuid() === 0) {
            $this->markTestSkipped(reason: 'Cannot test permission failure as root');
        }

        $result = new ChangePathPermissions()->execute(path: '/root', permissions: 0o644);

        self::assertFalse(condition: $result);
    }
}
