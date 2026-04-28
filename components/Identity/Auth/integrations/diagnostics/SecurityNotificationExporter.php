<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\Integrations\Diagnostics;

use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\AuditEvent;
use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\AuditExporterInterface;

/**
 * Routes selected high-signal audit events to a notification adapter.
 */
final readonly class SecurityNotificationExporter implements AuditExporterInterface
{
    public function __construct(private SendSecurityNotificationInterface $sender, private NormalizeAuditEvent $normalizeAuditEvent = new NormalizeAuditEvent()) {}

    public function export(array $events) : void
    {
        foreach ($events as $event) {
            $notification = $this->mapEvent(event: $event);

            if ($notification === null) {
                continue;
            }

            $this->sender->send(notification: $notification);
        }
    }

    private function mapEvent(AuditEvent $event) : SecurityNotification|null
    {
        $payload = $this->normalizeAuditEvent->execute(event: $event);

        return match ($event->name) {
            'auth.oauth.refresh.reuse_detected' => new SecurityNotification(
                name         : 'refresh_reuse_detected',
                severity     : 'critical',
                context      : $payload,
                correlationId: $event->correlationId
            ),
            'auth.admin.elevation.started'      => new SecurityNotification(
                name         : 'admin_elevation_started',
                severity     : 'high',
                context      : $payload,
                correlationId: $event->correlationId
            ),
            'auth.mfa.disabled',
            'auth.mfa.reset'                    => new SecurityNotification(
                name         : 'factor_removed',
                severity     : 'high',
                context      : $payload,
                correlationId: $event->correlationId
            ),
            'auth.password.changed'             => (($event->context['suspicious_activity'] ?? 0) === 1 || ($event->context['risk_action'] ?? null) === 'review')
                ? new SecurityNotification(
                    name         : 'password_changed_after_suspicious_activity',
                    severity     : 'high',
                    context      : $payload,
                    correlationId: $event->correlationId
                )
                : null,
            default                             => null,
        };
    }
}
