<?php

declare(strict_types=1);

namespace Avax\Tests\Architecture;

use Avax\Tooling\Refactor\CheckDuplicateOwners;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class DuplicateOwnersTest extends TestCase
{
    #[Test]
    public function duplicate_owner_checker_passes(): void
    {
        $result = (new CheckDuplicateOwners())->check();

        self::assertSame('PASS', $result['status']);
        self::assertSame([], $result['errors']);
    }
}
