<?php

declare(strict_types=1);

namespace Avax\Components\Database\System\Capabilities\ORM\DataLoader;

interface LoaderCallback
{
    public function __invoke(array $keys) : array;
}
