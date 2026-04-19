<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capability\Lifecycle;

use Avax\Auth\System\Capability\User\UserId;
use Avax\Auth\System\Capability\UserSource\ProvisionableUserSourceInterface;
use Avax\Auth\System\Flow\Diagnostics\AuditEvent;
use Avax\Auth\System\Flow\Diagnostics\AuditLogInterface;
use Avax\Auth\System\Foundation\Clock;

final readonly class LifecycleOrchestrator
{
    private Clock                            $clock;
    private AuditLogInterface                $auditLog;
    private LifecycleStoreInterface          $store;
    private ProvisionableUserSourceInterface $userSource;

    public function __construct(
        ProvisionableUserSourceInterface $userSource,
        LifecycleStoreInterface          $store,
        AuditLogInterface                $auditLog,
        Clock                            $clock
    )
    {
        $this->userSource = $userSource;
        $this->store      = $store;
        $this->auditLog   = $auditLog;
        $this->clock      = $clock;
    }

    public function activate(UserId $userId, LifecycleSource $source, string|null $reason = null) : LifecycleRecord
    {
        return $this->transition(userId: $userId, target: LifecycleState::ACTIVE, source: $source, reason: $reason);
    }

    private function transition(UserId $userId, LifecycleState $target, LifecycleSource $source, string|null $reason) : LifecycleRecord
    {
        $user = $this->userSource->findById(id: $userId);

        if ($user === null) {
            throw LifecycleFailed::userNotFound(userId: $userId->value);
        }

        $current = $this->store->find(userId: $userId)?->state ?? ($user->isActive() ? LifecycleState::ACTIVE : LifecycleState::SUSPENDED);

        if (! $this->isAllowedTransition(from: $current, to: $target)) {
            throw LifecycleFailed::transitionNotAllowed(from: $current, to: $target);
        }

        match ($target) {
            LifecycleState::ACTIVE        => $this->userSource->activate(id: $userId),
            LifecycleState::SUSPENDED     => $this->userSource->deactivate(id: $userId),
            LifecycleState::DEPROVISIONED => $this->deprovisionUser(userId: $userId),
        };

        $record = new LifecycleRecord(
            userId   : $userId->value,
            state    : $target,
            source   : $source,
            changedAt: $this->clock->now(),
            reason   : $reason
        );

        $this->store->save(record: $record);
        $this->auditLog->record(event: new AuditEvent(
                                           name      : 'auth.lifecycle.transitioned',
                                           occurredAt: $record->changedAt,
                                           context   : [
                                                           'user_id' => $record->userId,
                                                           'state'   => $record->state->value,
                                                           'source'  => $record->source->value,
                                                           'reason'  => $record->reason,
                                                       ]
                                       ));

        return $record;
    }

    private function isAllowedTransition(LifecycleState $from, LifecycleState $to) : bool
    {
        return match ($from) {
            LifecycleState::ACTIVE, LifecycleState::SUSPENDED => in_array($to, [LifecycleState::ACTIVE, LifecycleState::SUSPENDED, LifecycleState::DEPROVISIONED], true),
            LifecycleState::DEPROVISIONED                     => $to === LifecycleState::DEPROVISIONED,
        };
    }

    private function deprovisionUser(UserId $userId) : void
    {
        $this->userSource->replaceRoles(id: $userId, roles: []);
        $this->userSource->replacePermissions(id: $userId, permissions: []);
        $this->userSource->deactivate(id: $userId);
    }

    public function suspend(UserId $userId, LifecycleSource $source, string|null $reason = null) : LifecycleRecord
    {
        return $this->transition(userId: $userId, target: LifecycleState::SUSPENDED, source: $source, reason: $reason);
    }

    public function deprovision(UserId $userId, LifecycleSource $source, string|null $reason = null) : LifecycleRecord
    {
        return $this->transition(userId: $userId, target: LifecycleState::DEPROVISIONED, source: $source, reason: $reason);
    }

    public function read(UserId $userId) : LifecycleRecord|null
    {
        return $this->store->find(userId: $userId);
    }

    public function allowsAuthentication(UserId $userId) : bool
    {
        $record = $this->store->find(userId: $userId);

        return $record?->allowsAuthentication() ?? true;
    }
}
