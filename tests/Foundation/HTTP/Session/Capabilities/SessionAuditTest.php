<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\Tests\Session\Capabilities;

use Avax\HTTP\Session\SessionAudit\SessionAudit;
use PHPUnit\Framework\TestCase;

final class SessionAuditTest extends TestCase
{
    public function test_record_does_nothing_without_logger() : void
    {
        $audit = new SessionAudit(null);

        $audit->record('test.event');

        $this->assertTrue(true);
    }

    public function test_record_with_mock_logger() : void
    {
        $logger = new class {
            public ?string $message = null;

            public function info($message, $context = [])
            {
                $this->message = $message;
            }
        };

        $audit = new SessionAudit($logger);
        $audit->record('session.test', ['key' => 'value']);

        $this->assertNotNull($logger->message);
    }
}