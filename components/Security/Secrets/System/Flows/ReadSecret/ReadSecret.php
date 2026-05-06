<?php

declare(strict_types=1);

namespace Avax\Components\Security\Secrets\System\Flows\ReadSecret;

use Avax\Components\Security\Secrets\System\Capabilities\Stores\SecretStore;

final readonly class ReadSecret
{
    public function __construct(
        private SecretStore $secretStore,
    ) {
    }

    public function read(string $key): string
    {
        return $this->secretStore->get($key);
    }
}
