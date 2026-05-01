<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Realtime\System\Capabilities\Connections;

use Closure;

final readonly class Connection
{
    public string $id;

    public function __construct(private Closure $sender, ?string $id = null)
    {
        $this->id = $id ?? bin2hex(random_bytes(16));
    }

    public function send(mixed $message) : void
    {
        ($this->sender)($message);
    }
}
