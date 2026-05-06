<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\ORM\DataLoader;

use RuntimeException;

final class LoaderRegistry
{
    /** @var array<string, DataLoaderInterface> */
    private array $loaders = [];

    public function register(string $relation, DataLoaderInterface $dataLoader): self
    {
        $this->loaders[$relation] = $dataLoader;

        return $this;
    }

    public function get(string $relation): DataLoaderInterface
    {
        return $this->loaders[$relation]
            ?? throw new RuntimeException(message: 'No DataLoader registered for relation: '.$relation);
    }

    public function has(string $relation): bool
    {
        return isset($this->loaders[$relation]);
    }

    public function clear(?string $relation = null): self
    {
        if ($relation !== null) {
            unset($this->loaders[$relation]);

            return $this;
        }

        $this->loaders = [];

        return $this;
    }
}
