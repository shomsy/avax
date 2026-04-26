<?php

declare(strict_types=1);

namespace Avax\Tests\Foundation\HTTP\Session\Capabilities;

use Avax\HTTP\Session\SessionAudit\SessionAudit;
use Avax\Tests\TestCase;

final class SessionAuditTest extends TestCase
{
    public function test_record_does_nothing_without_logger() : void
    {
        $audit = new SessionAudit(logger: null);

        $audit->record(event: 'test.event');

        $this->assertTrue(true);
    }

    public function test_record_with_mock_logger() : void
    {
        $logger = new class {
            public string|null $message = null;

            public function info($message, $context = []) : void
            {
                $this->message = $message;
            }
        };

        $audit = new SessionAudit(logger: $logger);
        $audit->record(event: 'session.test', data: ['key' => 'value']);

        $this->assertNotNull($logger->message);
    }
}