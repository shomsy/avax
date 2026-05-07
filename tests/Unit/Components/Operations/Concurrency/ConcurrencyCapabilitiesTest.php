<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Operations\Concurrency;

use Avax\Components\Operations\Concurrency\System\System\Capabilities\Cancellation\CancellationToken;
use PHPUnit\Framework\TestCase;

final class ConcurrencyCapabilitiesTest extends TestCase
{
    public function test_cancellation_token_behavior() : void
    {
        $token = new CancellationToken();
        $this->assertFalse($token->isCancelled());

        $token->cancel();
        $this->assertTrue($token->isCancelled());
    }
}
