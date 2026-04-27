<?php

declare(strict_types=1);

namespace Avax\Components\Session\System\Capabilities\Storage;

use Avax\Components\Session\System\Capabilities\Security\SessionId;

final class SessionStore implements SessionStoreInterface
{
    private SessionScope|null $currentScope = null;

    public function open(string $sessionId): SessionScope
    {
        $this->currentScope = new SessionScope(
            id: new SessionId(value: $sessionId),
            data: $this->load(sessionId: $sessionId),
        );

        return $this->currentScope;
    }

    public function current(): SessionScope
    {
        if ($this->currentScope === null) {
            throw new \RuntimeException(message: 'No session is currently open.');
        }

        return $this->currentScope;
    }

    public function hasCurrent(): bool
    {
        return $this->currentScope !== null;
    }

    public function close(): void
    {
        if ($this->currentScope !== null) {
            $this->save(
                sessionId: $this->currentScope->id()->toString(),
                data: $this->currentScope->all(),
            );
        }

        $this->currentScope = null;
    }

    public function regenerate(string $oldSessionId): void
    {
        $this->close();

        $newId = bin2hex(string: random_bytes(length: 16));
        $this->open(sessionId: $newId);
    }

    public function destroy(): void
    {
        if ($this->currentScope !== null) {
            $this->destroyStorage(sessionId: $this->currentScope->id()->toString());
        }

        $this->currentScope = null;
    }

    private function load(string $sessionId): array
    {
        return [];
    }

    private function save(string $sessionId, array $data): void
    {
    }

    private function destroyStorage(string $sessionId): void
    {
    }
}