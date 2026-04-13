<?php

declare(strict_types=1);

namespace Avax\Auth\Tests\Integrations\Diagnostics;

use Avax\Auth\Integrations\Diagnostics\JsonLinesAuditExporter;
use Avax\Auth\Integrations\Diagnostics\NormalizeAuditEvent;
use Avax\Auth\Integrations\Diagnostics\PublishAuditMessageInterface;
use Avax\Auth\Integrations\Diagnostics\QueueAuditExporter;
use Avax\Auth\Integrations\Diagnostics\SecurityNotification;
use Avax\Auth\Integrations\Diagnostics\SecurityNotificationExporter;
use Avax\Auth\Integrations\Diagnostics\SendAuditWebhookInterface;
use Avax\Auth\Integrations\Diagnostics\SendSecurityNotificationInterface;
use Avax\Auth\Integrations\Diagnostics\SendSyslogMessageInterface;
use Avax\Auth\Integrations\Diagnostics\SyslogAuditExporter;
use Avax\Auth\Integrations\Diagnostics\WebhookAuditExporter;
use Avax\Auth\System\Flow\Diagnostics\AuditEvent;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use stdClass;

final class AuditExporterAdaptersTest extends TestCase
{
    public function testJsonLinesExporterMasksSensitiveContextAndChainsHashes() : void
    {
        $path = tempnam(sys_get_temp_dir(), 'auth-audit-');
        $this->assertIsString($path);

        $exporter = new JsonLinesAuditExporter($path);
        $exporter->export([
            new AuditEvent(
                name         : 'auth.login.succeeded',
                occurredAt   : new DateTimeImmutable('2026-04-13T10:00:00+00:00'),
                context      : ['email' => 'user@example.com', 'ip_address' => '127.0.0.1', 'risk_action' => 'allow'],
                correlationId: 'corr-1'
            ),
            new AuditEvent(
                name       : 'auth.logout.succeeded',
                occurredAt : new DateTimeImmutable('2026-04-13T10:01:00+00:00'),
                context    : ['user_id' => 1]
            ),
        ]);

        $lines = file($path, FILE_IGNORE_NEW_LINES);
        $this->assertIsArray($lines);
        $this->assertCount(2, $lines);

        $first = json_decode($lines[0], true, 512, JSON_THROW_ON_ERROR);
        $second = json_decode($lines[1], true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame('[redacted]', $first['context']['email']);
        $this->assertSame('[redacted]', $first['context']['ip_address']);
        $this->assertArrayHasKey('record_hash', $first);
        $this->assertArrayHasKey('record_hash', $second);
        $this->assertSame($first['record_hash'], $second['previous_hash']);

        @unlink($path);
    }

    public function testSyslogWebhookAndQueueExportersSendNormalizedPayloads() : void
    {
        $capture = new stdClass();
        $capture->syslog = [];
        $capture->webhook = [];
        $capture->queue = [];
        $event = new AuditEvent(
            name         : 'auth.oauth.refresh.reuse_detected',
            occurredAt   : new DateTimeImmutable('2026-04-13T11:00:00+00:00'),
            context      : ['refresh_token' => 'secret-token', 'user_id' => 1],
            correlationId: 'corr-2'
        );

        (new SyslogAuditExporter(
            sender: new class($capture) implements SendSyslogMessageInterface
            {
                public function __construct(private stdClass $capture) {}

                public function send(string $severity, string $message) : void
                {
                    $this->capture->syslog[] = [$severity, json_decode($message, true, 512, JSON_THROW_ON_ERROR)];
                }
            }
        ))->export([$event]);

        (new WebhookAuditExporter(
            sender: new class($capture) implements SendAuditWebhookInterface
            {
                public function __construct(private stdClass $capture) {}

                public function send(array $payload) : void
                {
                    $this->capture->webhook[] = $payload;
                }
            }
        ))->export([$event]);

        (new QueueAuditExporter(
            publisher: new class($capture) implements PublishAuditMessageInterface
            {
                public function __construct(private stdClass $capture) {}

                public function publish(string $topic, array $message) : void
                {
                    $this->capture->queue[] = [$topic, $message];
                }
            }
        ))->export([$event]);

        $this->assertSame('info', $capture->syslog[0][0]);
        $this->assertSame('[redacted]', $capture->syslog[0][1]['context']['refresh_token']);
        $this->assertSame('corr-2', $capture->webhook[0]['correlation_id']);
        $this->assertSame('auth.audit', $capture->queue[0][0]);
        $this->assertSame('auth.oauth.refresh.reuse_detected', $capture->queue[0][1]['name']);
    }

    public function testSecurityNotificationExporterRoutesHighSignalEvents() : void
    {
        $capture = new stdClass();
        $capture->notifications = [];

        $exporter = new SecurityNotificationExporter(
            sender: new class($capture) implements SendSecurityNotificationInterface
            {
                public function __construct(private stdClass $capture) {}

                public function send(SecurityNotification $notification) : void
                {
                    $this->capture->notifications[] = $notification;
                }
            },
            normalizeAuditEvent: new NormalizeAuditEvent()
        );

        $exporter->export([
            new AuditEvent('auth.admin.elevation.started', new DateTimeImmutable(), ['user_id' => 1], 'corr-3'),
            new AuditEvent('auth.password.changed', new DateTimeImmutable(), ['suspicious_activity' => 1], 'corr-3'),
            new AuditEvent('auth.login.succeeded', new DateTimeImmutable(), ['user_id' => 1], 'corr-3'),
        ]);

        $this->assertCount(2, $capture->notifications);
        $this->assertSame('admin_elevation_started', $capture->notifications[0]->name);
        $this->assertSame('password_changed_after_suspicious_activity', $capture->notifications[1]->name);
    }
}
