<?php

declare(strict_types=1);

namespace Avax\Tests\Foundation\Filesystem\Paths;

use Avax\Filesystem\Paths\PathHasPermissions;
use Avax\Tests\TestCase;
use PHPUnit\Framework\TestCase;

class PathHasPermissionsTest extends TestCase
{
    private string $testFile;

    protected function setUp() : void
    {
        parent::setUp();
        $this->testFile = '/home/shomsy/projects/components/tests/fixtures/Filesystem/has_perm_test.txt';
    }

    protected function tearDown() : void
    {
        @unlink(filename: $this->testFile);
        parent::tearDown();
    }

    public function testExecuteReturnsFalseForNonExistentPath() : void
    {
        $result = new PathHasPermissions()->execute(path: '/ne postoji put', permissions: 0755);

        self::assertFalse(condition: $result);
    }

    public function testExecuteReturnsTrueForMatchingPermissions() : void
    {
        file_put_contents(filename: $this->testFile, data: "sadrzaj\n");
        chmod(filename: $this->testFile, permissions: 0644);

        $result = new PathHasPermissions()->execute(path: $this->testFile, permissions: 0644);

        self::assertTrue(condition: $result);
    }

    public function testExecuteReturnsFalseForMismatchingPermissions() : void
    {
        file_put_contents(filename: $this->testFile, data: "sadrzaj\n");
        chmod(filename: $this->testFile, permissions: 0644);

        $result = new PathHasPermissions()->execute(path: $this->testFile, permissions: 0755);

        self::assertFalse(condition: $result);
    }
}