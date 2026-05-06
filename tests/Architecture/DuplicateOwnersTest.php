<?php

declare(strict_types=1);

namespace Avax\Tests\Architecture;

use Avax\Tooling\Refactor\CheckDuplicateOwners;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 2) . '/tooling/refactor/check-duplicate-owners.php';

final class DuplicateOwnersTest extends TestCase
{
    #[Test]
    public function duplicate_owner_checker_passes(): void
    {
        $result = (new CheckDuplicateOwners())->check();

        self::assertSame(expected: 'PASS', actual: $result['status']);
        self::assertSame(expected: [], actual: $result['errors']);
    }
}
