<?php

declare(strict_types=1);

namespace Avax\Components\Security\System\Secrets\Flows\ReadSecret;

use Avax\Components\Security\System\Secrets\Capabilities\Stores\SecretStore;

final readonly class ReadSecret
{
    public function __construct(
        private SecretStore $secretStore,
    ) {}

    public function read(string $key) : string
    {
        return $this->secretStore->get($key);
    }
}
