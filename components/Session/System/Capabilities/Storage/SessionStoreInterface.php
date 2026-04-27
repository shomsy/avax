<?php

declare(strict_types=1);

namespace Avax\Components\Session\System\Capabilities\Storage;

use Avax\Components\Session\System\Capabilities\Security\SessionId;

interface SessionStoreInterface
{
    public function open(string $sessionId): SessionScope;

    public function current(): SessionScope;

    public function hasCurrent(): bool;

    public function close(): void;

    public function regenerate(string $oldSessionId): void;

    public function destroy(): void;
}