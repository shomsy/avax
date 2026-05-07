<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Session\System\System\PublicSurface;

use Avax\Components\HTTP\Session\System\System\Capabilities\Audit\SessionAudit;
use Avax\Components\HTTP\Session\System\System\Capabilities\Events\SessionEventBus;
use Avax\Components\HTTP\Session\System\System\Capabilities\Metadata\SessionMetadata;
use Avax\Components\HTTP\Session\System\System\Capabilities\Transaction\SessionTransaction;
use Avax\Components\HTTP\Session\System\System\Foundation\SessionRecord;
use DateTimeImmutable;
use Psr\Log\LoggerInterface;

final class Session implements SessionInterface
{
    private ?SessionRecord $sessionRecord = null;

    private readonly SessionTransaction $sessionTransaction;

    public function __construct(
        private readonly SessionScope     $sessionScope,
        private readonly ?SessionMetadata $sessionMetadata = null,
        private readonly ?SessionAudit    $sessionAudit = null,
        private readonly ?SessionEventBus $sessionEventBus = null,
        private readonly ?LoggerInterface $logger = null,
    )
    {
        $this->sessionTransaction = new SessionTransaction($this->sessionScope);
    }

    public function start() : bool
    {
        $started = $this->sessionScope->start();

        if (! $started) {
            $this->sessionAudit?->record('session.start_failed');
            $this->sessionEventBus?->dispatch('session.start_failed');

            return false;
        }

        $data = $this->sessionScope->get('_record');

        if ($data instanceof SessionRecord) {
            $this->sessionRecord = $data;
            if ($this->sessionRecord->isExpired() || ($this->sessionMetadata instanceof SessionMetadata && ! $this->sessionMetadata->verify($this->sessionRecord->ipCreated ?? '', $this->sessionRecord->userAgentCreated ?? ''))) {
                $this->sessionAudit?->record('session.rejected', ['reason' => 'expired_or_mismatch']);
                $this->sessionEventBus?->dispatch('session.rejected', ['reason' => 'expired_or_mismatch']);
                $this->clear();
                $this->createNewRecord();
            } else {
                $this->touch();
                $id = $this->sessionRecord?->sessionId ?? '';
                $this->sessionAudit?->record('session.started', ['id' => $id]);
                $this->sessionEventBus?->dispatch('session.started', ['id' => $id]);
            }
        } else {
            $this->createNewRecord();
            $this->sessionAudit?->record('session.created', ['id' => $this->sessionRecord?->sessionId]);
            $this->sessionEventBus?->dispatch('session.created', ['id' => $this->sessionRecord?->sessionId]);
        }

        return true;
    }

    public function get(string $key, mixed $default = null) : mixed
    {
        $value = $this->sessionScope->get($key, $default);
        $this->sessionEventBus?->dispatch('session.retrieved', ['key' => $key]);

        return $value;
    }

    public function clear() : void
    {
        $this->flush();
    }

    public function flush() : void
    {
        $this->sessionScope->clear();
        $this->sessionRecord = null;
        $this->sessionAudit?->record('session.flushed');
        $this->sessionEventBus?->dispatch('session.flushed');
    }

    private function createNewRecord() : void
    {
        $now                 = new DateTimeImmutable();
        $this->sessionRecord = new SessionRecord(
            sessionId        : bin2hex(random_bytes(16)),
            createdAt        : $now,
            lastSeenAt       : $now,
            idleExpiresAt    : $now->modify('+30 minutes'),
            absoluteExpiresAt: $now->modify('+24 hours'),
            ipCreated        : $this->sessionMetadata?->getIp(),
            userAgentCreated : $this->sessionMetadata?->getUserAgent(),
        );
        $this->saveRecord();
    }

    private function saveRecord() : void
    {
        if ($this->sessionRecord instanceof SessionRecord) {
            $this->sessionScope->set('_record', $this->sessionRecord);
        }
    }

    public function set(string $key, mixed $value) : void
    {
        $this->put($key, $value);
    }

    public function put(string $key, mixed $value) : void
    {
        $this->sessionScope->set($key, $value);
        $this->sessionAudit?->record('session.put', ['key' => $key]);
        $this->sessionEventBus?->dispatch('session.put', ['key' => $key]);
    }

    private function touch() : void
    {
        if (! $this->sessionRecord instanceof SessionRecord) {
            return;
        }

        $now                 = new DateTimeImmutable();
        $this->sessionRecord = new SessionRecord(
            sessionId        : $this->sessionRecord->sessionId,
            createdAt        : $this->sessionRecord->createdAt,
            lastSeenAt       : $now,
            idleExpiresAt    : $now->modify('+30 minutes'),
            absoluteExpiresAt: $this->sessionRecord->absoluteExpiresAt,
            ipCreated        : $this->sessionRecord->ipCreated,
            userAgentCreated : $this->sessionRecord->userAgentCreated,
        );
        $this->saveRecord();
    }

    public function isStarted() : bool
    {
        return $this->sessionScope->isStarted();
    }

    public function has(string $key) : bool
    {
        return $this->sessionScope->has($key);
    }

    public function all() : array
    {
        return $this->sessionScope->all();
    }

    public function destroy() : void
    {
        $this->sessionScope->destroy();
        $this->sessionRecord = null;
        $this->sessionAudit?->record('session.destroyed');
        $this->sessionEventBus?->dispatch('session.destroyed');
    }

    public function regenerate(bool $destroy = false) : bool
    {
        $oldId  = $this->id();
        $result = $this->sessionScope->regenerate($destroy);

        if ($result) {
            $this->createNewRecord();
            $this->sessionAudit?->record('session.regenerated', ['old_id' => $oldId, 'new_id' => $this->id()]);
            $this->sessionEventBus?->dispatch('session.regenerated', ['old_id' => $oldId, 'new_id' => $this->id()]);
            $this->logger?->info('Session regenerated: ' . $this->id());
        }

        return $result;
    }

    public function id() : string
    {
        return $this->sessionScope->id() ?: ($this->sessionRecord?->sessionId ?? '');
    }

    public function save() : void
    {
        $this->saveRecord();
        $this->sessionScope->save();
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

    public function forget(string $key) : void
    {
        $this->sessionScope->forget($key);
        $this->sessionAudit?->record('session.forget', ['key' => $key]);
        $this->sessionEventBus?->dispatch('session.forget', ['key' => $key]);
    }

    public function getFlash(string $key, mixed $default = null) : mixed
    {
        $current = $this->get('_flash_current', []);

        return $current[$key] ?? $default;
    }

    public function transaction() : SessionTransaction
    {
        return $this->sessionTransaction;
    }

    public function events() : SessionEventBus
    {
        return $this->sessionEventBus ?? new SessionEventBus();
    }
}
