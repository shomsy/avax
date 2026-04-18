<?php

declare(strict_types=1);

namespace Avax\HTTP\Request\Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * PublicEntryPointTest - REMOVED.
 *
 * PublicEntryPointRequest has been removed from the codebase.
 * The canonical entry flow is now AssembleIncomingRequest.
 *
 * @see AssembleIncomingRequestTest
 */
class PublicEntryPointTest extends TestCase
{
    public function test_this_test_class_is_intentionally_empty(): void
    {
        $this->markTestSkipped(
            'PublicEntryPointRequest removed. See AssembleIncomingRequestTest for canonical entry flow tests.'
        );
    }
}
