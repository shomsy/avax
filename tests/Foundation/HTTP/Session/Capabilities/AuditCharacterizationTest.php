<?php

declare(strict_types=1);

use Avax\HTTP\Session\Audit\Audit;
use Avax\Tests\TestCase;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

final class AuditCharacterizationTest extends TestCase
{
    public function test_audit_records_to_psr_logger() : void
    {
        $logger = new class implements LoggerInterface {
            public array $calls = [];

            public function emergency($message, array $context = []) { $this->calls[] = ['level' => 'emergency', 'msg' => $message, 'ctx' => $context]; }

            public function alert($message, array $context = []) { $this->calls[] = ['level' => 'alert', 'msg' => $message, 'ctx' => $context]; }

            public function critical($message, array $context = []) { $this->calls[] = ['level' => 'critical', 'msg' => $message, 'ctx' => $context]; }

            public function error($message, array $context = []) { $this->calls[] = ['level' => 'error', 'msg' => $message, 'ctx' => $context]; }

            public function warning($message, array $context = []) { $this->calls[] = ['level' => 'warning', 'msg' => $message, 'ctx' => $context]; }

            public function notice($message, array $context = []) { $this->calls[] = ['level' => 'notice', 'msg' => $message, 'ctx' => $context]; }

            public function info($message, array $context = []) { $this->calls[] = ['level' => 'info', 'msg' => $message, 'ctx' => $context]; }

            public function debug($message, array $context = []) { $this->calls[] = ['level' => 'debug', 'msg' => $message, 'ctx' => $context]; }

            public function log($level, $message, array $context = []) { $this->calls[] = ['level' => $level, 'msg' => $message, 'ctx' => $context]; }
        };

        $audit = new Audit(logger: $logger);
        $audit->record(event: 'user_login', data: ['user_id' => 7, 'token' => 'secret']);

        $this->assertNotEmpty($logger->calls, 'logger should have been called');
        $found = false;
        foreach ($logger->calls as $c) {
            if ($c['level'] === 'info' && isset($c['ctx']['event']) && $c['ctx']['event'] === 'USER_LOGIN') {
                $found = true;
                $this->assertArrayHasKey('action_data', $c['ctx']);
                $this->assertStringContainsString('***MASKED***', json_encode($c['ctx']['action_data']));
            }
        }

        $this->assertTrue($found, 'Audit did not log expected event as info with USER_LOGIN');
    }
}
