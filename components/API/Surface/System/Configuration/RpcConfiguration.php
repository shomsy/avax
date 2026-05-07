<?php

declare(strict_types=1);

namespace Avax\Components\API\Surface\System\Configuration;

final readonly class RpcConfiguration
{
    public function __construct(
        public string $endpoint = '/rpc',
        public bool   $strictValidation = true,
        public int    $maxBatchSize = 10,
    ) {}
}
