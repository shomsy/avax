<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\ORM;

use Closure;
use Override;
use RuntimeException;

final class HasMany extends Relation
{
    /**
     * @param (Closure(string, string, string, string): array)|null $loader
     */
    public function __construct(
        string                        $parent,
        string                        $related,
        string                        $foreignKey,
        string                        $localKey,
        private readonly Closure|null $loader = null,
    )
    {
        parent::__construct(parent: $parent, related: $related, foreignKey: $foreignKey, localKey: $localKey);
    }

    #[Override]
    public function getResults(): array
    {
        if (!$this->loader instanceof Closure) {
            throw new RuntimeException(message: 'HasMany relation requires a loader before results can be read.');
        }

        return ($this->loader)(
            $this->parent,
            $this->related,
            $this->foreignKey,
            $this->localKey,
        );
    }
}
