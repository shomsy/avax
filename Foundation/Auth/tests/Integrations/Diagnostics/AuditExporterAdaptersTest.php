<?php

declare(strict_types=1);

namespace Avax\Auth\Tests\Integrations\Diagnostics;

use Avax\Auth\Integrations\Diagnostics\ContextFlagAuditLegalHoldPolicy;
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
use Avax\Auth\System\Capabilities\Diagnostics\Audit\AuditEvent;
use DateTimeImmutable;
use JsonException;
use PHPUnit\Framework\TestCase;
use stdClass;

final class AuditExporterAdaptersTest extends TestCase
{
    /**
     * @throws JsonException
     */
    public function testJsonLinesExporterMasksSensitiveContextAndChainsHashes() : void
    {
        $path = tempnam(sys_get_temp_dir(), 'auth-audit-');
        $this->assertIsString(actual: $path);

        $exporter = new JsonLinesAuditExporter(path: $path);
        $exporter->export(events: [
                                      new AuditEvent(
                                          name         : 'auth.login.succeeded',
                                          occurredAt   : new DateTimeImmutable(datetime: '2026-04-13T10:00:00+00:00'),
                                          context      : ['email' => 'user@example.com', 'ip_address' => '127.0.0.1', 'risk_action' => 'allow'],
                                          correlationId: 'corr-1'
                                      ),
                                      new AuditEvent(
                                          name      : 'auth.logout.succeeded',
                                          occurredAt: new DateTimeImmutable(datetime: '2026-04-13T10:01:00+00:00'),
                                          context   : ['user_id' => 1]
                                      ),
                                  ]);

        $lines = file($path, FILE_IGNORE_NEW_LINES);
        $this->assertIsArray(actual: $lines);
        $this->assertCount(expectedCount: 2, haystack: $lines);

        $first  = json_decode($lines[0], true, 512, JSON_THROW_ON_ERROR);
        $second = json_decode($lines[1], true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame(expected: '[redacted]', actual: $first['context']['email']);
        $this->assertSame(expected: '[redacted]', actual: $first['context']['ip_address']);
        $this->assertArrayHasKey(key: 'record_hash', array: $first);
        $this->assertArrayHasKey(key: 'record_hash', array: $second);
        $this->assertSame(expected: $first['record_hash'], actual: $second['previous_hash']);

        unlink($path);
    }

    /**
     * @throws JsonException
     */
    public function testSyslogWebhookAndQueueExportersSendNormalizedPayloads() : void
    {
        $capture          = new stdClass();
        $capture->syslog  = [];
        $capture->webhook = [];
        $capture->queue   = [];
        $event            = new AuditEvent(
            name         : 'auth.oauth.refresh.reuse_detected',
            occurredAt   : new DateTimeImmutable(datetime: '2026-04-13T11:00:00+00:00'),
            context      : ['refresh_token' => 'secret-token', 'user_id' => 1],
            correlationId: 'corr-2'
        );

        (new SyslogAuditExporter(
            sender: new class($capture) implements SendSyslogMessageInterface {
                      private stdClass $capture;

                      public function __construct(stdClass $capture) { $this->capture = $capture; }

                      public function send(string $severity, string $message) : void
                      {
                          $this->capture->syslog[] = [$severity, json_decode($message, true, 512, JSON_THROW_ON_ERROR)];
                      }
                  }
        ))->export(events: [$event]);

        (new WebhookAuditExporter(
            sender: new class($capture) implements SendAuditWebhookInterface {
                      private stdClass $capture;

                      public function __construct(stdClass $capture) { $this->capture = $capture; }

                      public function send(array $payload) : void
                      {
                          $this->capture->webhook[] = $payload;
                      }
                  }
        ))->export(events: [$event]);

        (new QueueAuditExporter(
            publisher: new class($capture) implements PublishAuditMessageInterface {
                         private stdClass $capture;

                         public function __construct(stdClass $capture) { $this->capture = $capture; }

                         public function publish(string $topic, array $message) : void
                         {
                             $this->capture->queue[] = [$topic, $message];
                         }
                     }
        ))->export(events: [$event]);

        $this->assertSame(expected: 'info', actual: $capture->syslog[0][0]);
        $this->assertSame(expected: '[redacted]', actual: $capture->syslog[0][1]['context']['refresh_token']);
        $this->assertSame(expected: 'corr-2', actual: $capture->webhook[0]['correlation_id']);
        $this->assertSame(expected: 'auth.audit', actual: $capture->queue[0][0]);
        $this->assertSame(expected: 'auth.oauth.refresh.reuse_detected', actual: $capture->queue[0][1]['name']);
    }

    /**
     * @throws JsonException
     */
    public function testLegalHoldPreservesSensitiveContextForForensicExport() : void
    {
        $path = tempnam(sys_get_temp_dir(), 'auth-audit-hold-');
        $this->assertIsString(actual: $path);

        $exporter = new JsonLinesAuditExporter(
            path               : $path,
            normalizeAuditEvent: new NormalizeAuditEvent(
                                     legalHoldPolicy: new ContextFlagAuditLegalHoldPolicy()
                                 )
        );
        $exporter->export(events: [
                                      new AuditEvent(
                                          name      : 'auth.admin.incident.review_opened',
                                          occurredAt: new DateTimeImmutable(datetime: '2026-04-13T12:00:00+00:00'),
                                          context   : [
                                                          'email'      => 'admin@example.com',
                                                          'ip_address' => '127.0.0.1',
                                                          'legal_hold' => 1,
                                                      ]
                                      ),
                                  ]);

        $lines = file($path, FILE_IGNORE_NEW_LINES);
        $this->assertIsArray(actual: $lines);
        $payload = json_decode($lines[0], true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame(expected: 'admin@example.com', actual: $payload['context']['email']);
        $this->assertSame(expected: '127.0.0.1', actual: $payload['context']['ip_address']);

        unlink($path);
    }

    public function testSecurityNotificationExporterRoutesHighSignalEvents() : void
    {
        $capture                = new stdClass();
        $capture->notifications = [];

        $exporter = new SecurityNotificationExporter(
            sender             : new class($capture) implements SendSecurityNotificationInterface {
                                   private stdClass $capture;

                                   public function __construct(stdClass $capture) { $this->capture = $capture; }

                                   public function send(SecurityNotification $notification) : void
                                   {
                                       $this->capture->notifications[] = $notification;
                                   }
                               },
            normalizeAuditEvent: new NormalizeAuditEvent()
        );

        $exporter->export(events: [
                                      new AuditEvent(name: 'auth.admin.elevation.started', occurredAt: new DateTimeImmutable(), context: ['user_id' => 1], correlationId: 'corr-3'),
                                      new AuditEvent(name: 'auth.password.changed', occurredAt: new DateTimeImmutable(), context: ['suspicious_activity' => 1], correlationId: 'corr-3'),
                                      new AuditEvent(name: 'auth.login.succeeded', occurredAt: new DateTimeImmutable(), context: ['user_id' => 1], correlationId: 'corr-3'),
                                  ]);

        $this->assertCount(expectedCount: 2, haystack: $capture->notifications);
        $this->assertSame(expected: 'admin_elevation_started', actual: $capture->notifications[0]->name);
        $this->assertSame(expected: 'password_changed_after_suspicious_activity', actual: $capture->notifications[1]->name);
    }
}
