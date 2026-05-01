<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\ORM\DataLoader;

interface LoaderCallback
{
    public function __invoke(array $keys): array;
}
