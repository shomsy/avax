<?php

declare(strict_types=1);

namespace Avax\Components\Application\Config\System\Configuration;

use Avax\Components\DataStack\Data\System\Capabilities\Forms\CollectionForm\Collection;
use InvalidArgumentException;

interface ConfiguratorInterface
{
    public function configurationFilePaths(): array;

    public function get(string $key, mixed $default = null): mixed;

    public function has(string $key): bool;

    public function all(): Collection;

    public function refresh(): Collection;
}
