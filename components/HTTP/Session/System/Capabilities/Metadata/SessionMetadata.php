<?php
declare(strict_types=1);

namespace Avax\Components\HTTP\Session\System\Capabilities\Metadata;

final readonly class SessionMetadata
{
    public function __construct(
        private array $server
    ) {}

    public function verify(string $expectedIp, string $expectedUserAgent) : bool
    {
        return $this->getIp() === $expectedIp
            && $this->getUserAgent() === $expectedUserAgent;
    }

    public function getIp() : ?string
    {
        return $this->server['REMOTE_ADDR'] ?? null;
    }

    public function getUserAgent() : ?string
    {
        return $this->server['HTTP_USER_AGENT'] ?? null;
    }
}
