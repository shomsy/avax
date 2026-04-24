<?php

declare(strict_types=1);

namespace Avax\Tests\Foundation\Filesystem\Paths;

use Avax\Filesystem\Paths\ChangePathPermissions;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class ChangePathPermissionsTest extends TestCase
{
    private string $testFile;

    protected function setUp() : void
    {
        parent::setUp();
        $this->testFile = '/home/shomsy/projects/components/tests/fixtures/Filesystem/perm_test.txt';
    }

    protected function tearDown() : void
    {
        @unlink(filename: $this->testFile);
        parent::tearDown();
    }

    public function testExecuteSetsPermissions() : void
    {
        file_put_contents(filename: $this->testFile, data: "sadrzaj\n");

        $result = (new ChangePathPermissions())->execute(path: $this->testFile, permissions: 0644);

        self::assertTrue(condition: $result);
    }

    public function testExecuteThrowsExceptionForNonExistentPath() : void
    {
        $this->expectException(exception: RuntimeException::class);
        $this->expectExceptionMessage(message: 'Path does not exist:');

        (new ChangePathPermissions())->execute(path: '/ne postoji put', permissions: 0644);
    }

    public function testExecuteReturnsFalseForFailure() : void
    {
        if (posix_getuid() === 0) {
            $this->markTestSkipped(reason: 'Cannot test permission failure as root');
        }

        $result = (new ChangePathPermissions())->execute(path: '/root', permissions: 0644);

        self::assertFalse(condition: $result);
    }
}