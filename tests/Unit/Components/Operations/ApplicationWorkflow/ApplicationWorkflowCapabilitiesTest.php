<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Operations\ApplicationWorkflow;

use Avax\Components\Operations\ApplicationWorkflow\System\Capabilities\Idempotency\IdempotencyKey;
use PHPUnit\Framework\TestCase;

final class ApplicationWorkflowCapabilitiesTest extends TestCase
{
    public function test_idempotency_key_identification() : void
    {
        $key = new IdempotencyKey('saga-1', 'step-1', '0');
        $this->assertSame('saga-1:step-1:0', $key->toString());
    }
}
