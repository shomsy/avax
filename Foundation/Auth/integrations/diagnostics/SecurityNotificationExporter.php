<?php

declare(strict_types=1);

namespace Avax\Auth\Integrations\Diagnostics;

use Avax\Auth\System\Flow\Diagnostics\AuditEvent;
use Avax\Auth\System\Flow\Diagnostics\AuditExporterInterface;

/**
 * Routes selected high-signal audit events to a notification adapter.
 */
final readonly class SecurityNotificationExporter implements AuditExporterInterface
{
    private NormalizeAuditEvent               $normalizeAuditEvent;
    private SendSecurityNotificationInterface $sender;

    public function __construct(
        SendSecurityNotificationInterface $sender,
        NormalizeAuditEvent               $normalizeAuditEvent = new NormalizeAuditEvent()
    )
    {
        $this->sender              = $sender;
        $this->normalizeAuditEvent = $normalizeAuditEvent;
    }

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
