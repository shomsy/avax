<?php

declare(strict_types=1);

namespace Avax\Tooling\DependencyMap\System\Capabilities\Graph;

final readonly class DependencyNode
{
    /**
     * @param list<string> $dependsOn
     */
    public function __construct(
        public string $name,
        public array  $dependsOn = [],
    )
    {
    }
}
