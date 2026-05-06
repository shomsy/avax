<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\Lifecycle;

use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\AuditEvent;
use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\AuditLogInterface;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\User;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\UserId;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\UserSource\ProvisionableUserSourceInterface;
use Avax\Components\Identity\Auth\System\Foundation\Clock;

final readonly class LifecycleOrchestrator
{
    public function __construct(private ProvisionableUserSourceInterface $provisionableUserSource, private LifecycleStoreInterface $lifecycleStore, private AuditLogInterface $auditLog, private Clock $clock)
    {
    }

    public function activate(UserId $userId, LifecycleSource $lifecycleSource, ?string $reason = null): LifecycleRecord
    {
        return $this->transition(userId: $userId, reason: $reason, target: LifecycleState::ACTIVE, source: $lifecycleSource);
    }

    private function transition(UserId $userId, LifecycleState $lifecycleState, LifecycleSource $lifecycleSource, ?string $reason): LifecycleRecord
    {
        $user = $this->provisionableUserSource->findById(id: $userId);

        if (! $user instanceof User) {
            throw LifecycleFailed::userNotFound(userId: $userId->value);
        }

        $currentRecord = $this->lifecycleStore->find(userId: $userId);
        $current = $currentRecord instanceof LifecycleRecord ? $currentRecord->state : ($user->isActive() ? LifecycleState::ACTIVE : LifecycleState::SUSPENDED);

        if (! $this->isAllowedTransition(from: $current, to: $lifecycleState)) {
            throw LifecycleFailed::transitionNotAllowed(from: $current, to: $lifecycleState);
        }

        match ($lifecycleState) {
            LifecycleState::ACTIVE => $this->provisionableUserSource->activate(id: $userId),
            LifecycleState::SUSPENDED => $this->provisionableUserSource->deactivate(id: $userId),
            LifecycleState::DEPROVISIONED => $this->deprovisionUser(userId: $userId),
        };

        $lifecycleRecord = new LifecycleRecord(
            userId   : $userId->value,
            state    : $lifecycleState,
            source   : $lifecycleSource,
            changedAt: $this->clock->now(),
            reason   : $reason,
        );

        $this->lifecycleStore->save(record: $lifecycleRecord);
        $this->auditLog->record(event: new AuditEvent(
            name      : 'auth.lifecycle.transitioned',
            occurredAt: $lifecycleRecord->changedAt,
            context   : [
                'user_id' => $lifecycleRecord->userId,
                'state' => $lifecycleRecord->state->value,
                'source' => $lifecycleRecord->source->value,
                'reason' => $lifecycleRecord->reason,
            ],
        ));

        return $lifecycleRecord;
    }

    private function isAllowedTransition(LifecycleState $from, LifecycleState $to): bool
    {
        return match ($from) {
            LifecycleState::ACTIVE, LifecycleState::SUSPENDED => in_array(needle: $to, haystack: [LifecycleState::ACTIVE, LifecycleState::SUSPENDED, LifecycleState::DEPROVISIONED], strict: true),
            LifecycleState::DEPROVISIONED => $to === LifecycleState::DEPROVISIONED,
        };
    }

    private function deprovisionUser(UserId $userId): void
    {
        $this->provisionableUserSource->replaceRoles(roles: [], id: $userId);
        $this->provisionableUserSource->replacePermissions(permissions: [], id: $userId);
        $this->provisionableUserSource->deactivate(id: $userId);
    }

    public function suspend(UserId $userId, LifecycleSource $lifecycleSource, ?string $reason = null): LifecycleRecord
    {
        return $this->transition(userId: $userId, reason: $reason, target: LifecycleState::SUSPENDED, source: $lifecycleSource);
    }

    public function deprovision(UserId $userId, LifecycleSource $lifecycleSource, ?string $reason = null): LifecycleRecord
    {
        return $this->transition(userId: $userId, reason: $reason, target: LifecycleState::DEPROVISIONED, source: $lifecycleSource);
    }

    public function read(UserId $userId): ?LifecycleRecord
    {
        return $this->lifecycleStore->find(userId: $userId);
    }

    public function allowsAuthentication(UserId $userId): bool
    {
        $record = $this->lifecycleStore->find(userId: $userId);

        return $record?->allowsAuthentication() ?? true;
    }
}
