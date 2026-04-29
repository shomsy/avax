<?php
declare(strict_types=1);

namespace Avax\Components\HTTP\Session\System\PublicSurface;

use Avax\Components\HTTP\Session\System\Capabilities\Audit\SessionAudit;
use Avax\Components\HTTP\Session\System\Capabilities\Events\SessionEventBus;
use Avax\Components\HTTP\Session\System\Capabilities\Metadata\SessionMetadata;
use Avax\Components\HTTP\Session\System\Capabilities\Storage\SessionStoreInterface;
use Avax\Components\HTTP\Session\System\Capabilities\Transaction\SessionTransaction;
use Avax\Components\HTTP\Session\System\Foundation\SessionRecord;
use DateTimeImmutable;
use Psr\Log\LoggerInterface;

final class Session
{
    private ?SessionRecord     $record = null;
    private SessionTransaction $transaction;

    public function __construct(
        private readonly SessionStoreInterface $store,
        private readonly SessionMetadata       $metadata,
        private readonly SessionAudit          $audit,
        private readonly SessionEventBus       $events,
        private readonly ?LoggerInterface      $logger = null
    )
    {
        $this->transaction = new SessionTransaction($this->store);
    }

    public function start() : void
    {
        $data = $this->store->get('_record');

        if ($data instanceof SessionRecord) {
            $this->record = $data;
            if ($this->record->isExpired() || ! $this->metadata->verify($this->record->ipCreated, $this->record->userAgentCreated)) {
                $this->audit->record('session.rejected', ['reason' => 'expired_or_mismatch']);
                $this->events->dispatch('session.rejected', ['reason' => 'expired_or_mismatch']);
                $this->flush();
                $this->createNewRecord();
            } else {
                $this->touch();
                $this->audit->record('session.started', ['id' => $this->record->sessionId]);
                $this->events->dispatch('session.started', ['id' => $this->record->sessionId]);
            }
        } else {
            $this->createNewRecord();
            $this->audit->record('session.created', ['id' => $this->record->sessionId]);
            $this->events->dispatch('session.created', ['id' => $this->record->sessionId]);
        }
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
            ipCreated        : $this->metadata->getIp(),
            userAgentCreated : $this->metadata->getUserAgent()
        );
        $this->saveRecord();
    }

    private function touch() : void
    {
        $now          = new DateTimeImmutable();
        $this->record = new SessionRecord(
            ...((array) $this->record),
            lastSeenAt   : $now,
            idleExpiresAt: $now->modify('+30 minutes')
        );
        $this->saveRecord();
    }

    private function saveRecord() : void
    {
        $this->store->put('_record', $this->record);
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $value = $this->store->get($key, $default);
        $this->events->dispatch('session.retrieved', ['key' => $key]);

        return $value;
    }

    public function put(string $key, mixed $value) : void
    {
        $this->store->put($key, $value);
        $this->audit->record('session.put', ['key' => $key]);
        $this->events->dispatch('session.put', ['key' => $key]);
    }

    public function forget(string $key): void
    {
        $this->store->forget($key);
        $this->audit->record('session.forget', ['key' => $key]);
        $this->events->dispatch('session.forget', ['key' => $key]);
    }

    public function flash(string $key, mixed $value) : void
    {
        $this->put("_flash_next.$key", $value);
    }

    public function ageFlash() : void
    {
        $current = $this->get("_flash_next", []);
        $this->put("_flash_current", $current);
        $this->forget("_flash_next");
    }

    public function getFlash(string $key, mixed $default = null) : mixed
    {
        return $this->get("_flash_current.$key", $default);
    }

    public function regenerate() : void
    {
        $oldId = $this->id();
        $this->createNewRecord();
        $this->audit->record('session.regenerated', ['old_id' => $oldId, 'new_id' => $this->id()]);
        $this->events->dispatch('session.regenerated', ['old_id' => $oldId, 'new_id' => $this->id()]);
        $this->logger?->info("Session regenerated: {$this->id()}");
    }

    public function flush() : void
    {
        $this->store->flush();
        $this->record = null;
        $this->audit->record('session.flushed');
        $this->events->dispatch('session.flushed');
    }

    public function id() : string
    {
        return $this->record?->sessionId ?? ''; 
    }

    public function transaction() : SessionTransaction
    {
        return $this->transaction;
    }

    public function events() : SessionEventBus
    {
        return $this->events;
    }
}
