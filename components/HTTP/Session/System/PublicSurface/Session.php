<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Session\System\PublicSurface;

use Avax\Components\HTTP\Session\System\Capabilities\Audit\SessionAudit;
use Avax\Components\HTTP\Session\System\Capabilities\Events\SessionEventBus;
use Avax\Components\HTTP\Session\System\Capabilities\Metadata\SessionMetadata;
use Avax\Components\HTTP\Session\System\Capabilities\Transaction\SessionTransaction;
use Avax\Components\HTTP\Session\System\Foundation\SessionRecord;
use DateTimeImmutable;
use Psr\Log\LoggerInterface;

final class Session implements SessionInterface
{
    private ?SessionRecord $record = null;
    private SessionTransaction $transaction;

    public function __construct(
        private readonly SessionScope     $scope,
        private readonly ?SessionMetadata $metadata = null,
        private readonly ?SessionAudit    $audit = null,
        private readonly ?SessionEventBus $events = null,
        private readonly ?LoggerInterface $logger = null
    )
    {
        $this->transaction = new SessionTransaction($this->scope);
    }

    public function start() : bool
    {
        $started = $this->scope->start();

        if (! $started) {
            $this->audit?->record('session.start_failed');
            $this->events?->dispatch('session.start_failed');

            return false;
        }

        $data = $this->scope->get('_record');

        if ($data instanceof SessionRecord) {
            $this->record = $data;
            if ($this->record->isExpired() || ($this->metadata !== null && ! $this->metadata->verify($this->record->ipCreated ?? '', $this->record->userAgentCreated ?? ''))) {
                $this->audit?->record('session.rejected', ['reason' => 'expired_or_mismatch']);
                $this->events?->dispatch('session.rejected', ['reason' => 'expired_or_mismatch']);
                $this->clear();
                $this->createNewRecord();
            } else {
                $this->touch();
                $this->audit?->record('session.started', ['id' => $this->record->sessionId]);
                $this->events?->dispatch('session.started', ['id' => $this->record->sessionId]);
            }
        } else {
            $this->createNewRecord();
            $this->audit?->record('session.created', ['id' => $this->record?->sessionId]);
            $this->events?->dispatch('session.created', ['id' => $this->record?->sessionId]);
        }

        return true;
    }

    public function isStarted() : bool
    {
        return $this->scope->isStarted();
    }

    private function createNewRecord() : void
    {
        $now          = new DateTimeImmutable();
        $this->record = new SessionRecord(
            sessionId        : bin2hex(random_bytes(16)),
            createdAt        : $now,
            lastSeenAt       : $now,
            idleExpiresAt    : $now->modify('+30 minutes'),
            absoluteExpiresAt: $now->modify('+24 hours'),
            ipCreated        : $this->metadata?->getIp(),
            userAgentCreated : $this->metadata?->getUserAgent()
        );
        $this->saveRecord();
    }

    private function touch() : void
    {
        if ($this->record === null) {
            return;
        }

        $now = new DateTimeImmutable();
        $this->record = new SessionRecord(
            ...((array) $this->record),
            lastSeenAt   : $now,
            idleExpiresAt: $now->modify('+30 minutes')
        );
        $this->saveRecord();
    }

    private function saveRecord() : void
    {
        if ($this->record !== null) {
            $this->scope->set('_record', $this->record);
        }
    }

    public function id() : string
    {
        return $this->scope->id() ?: ($this->record?->sessionId ?? '');
    }

    public function has(string $key) : bool
    {
        return $this->scope->has($key);
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $value = $this->scope->get($key, $default);
        $this->events?->dispatch('session.retrieved', ['key' => $key]);

        return $value;
    }

    public function all() : array
    {
        return $this->scope->all();
    }

    public function set(string $key, mixed $value) : void
    {
        $this->put($key, $value);
    }

    public function put(string $key, mixed $value) : void
    {
        $this->scope->set($key, $value);
        $this->audit?->record('session.put', ['key' => $key]);
        $this->events?->dispatch('session.put', ['key' => $key]);
    }

    public function forget(string $key): void
    {
        $this->scope->forget($key);
        $this->audit?->record('session.forget', ['key' => $key]);
        $this->events?->dispatch('session.forget', ['key' => $key]);
    }

    public function clear() : void
    {
        $this->flush();
    }

    public function flush() : void
    {
        $this->scope->clear();
        $this->record = null;
        $this->audit?->record('session.flushed');
        $this->events?->dispatch('session.flushed');
    }

    public function destroy() : void
    {
        $this->scope->destroy();
        $this->record = null;
        $this->audit?->record('session.destroyed');
        $this->events?->dispatch('session.destroyed');
    }

    public function regenerate(bool $destroy = false) : bool
    {
        $oldId = $this->id();
        $result = $this->scope->regenerate($destroy);

        if ($result) {
            $this->createNewRecord();
            $this->audit?->record('session.regenerated', ['old_id' => $oldId, 'new_id' => $this->id()]);
            $this->events?->dispatch('session.regenerated', ['old_id' => $oldId, 'new_id' => $this->id()]);
            $this->logger?->info("Session regenerated: {$this->id()}");
        }

        return $result;
    }

    public function save() : void
    {
        $this->saveRecord();
        $this->scope->save();
    }

    public function flash(string $key, mixed $value) : void
    {
        $flashes       = $this->get('_flash_next', []);
        $flashes[$key] = $value;
        $this->put('_flash_next', $flashes);
    }

    public function ageFlash() : void
    {
        $next = $this->get('_flash_next', []);
        $this->put('_flash_current', $next);
        $this->forget('_flash_next');
    }

    public function getFlash(string $key, mixed $default = null) : mixed
    {
        $current = $this->get('_flash_current', []);

        return $current[$key] ?? $default;
    }

    public function transaction() : SessionTransaction
    {
        return $this->transaction;
    }

    public function events() : SessionEventBus
    {
        return $this->events ?? new SessionEventBus();
    }
}
