<?php

declare(strict_types=1);

namespace Avax\Components\DeveloperTools\Dx\System\Capabilities\Capabilities\Graph;

final readonly class DependencyNode
{
    /**
     * @param list<string> $dependsOn
     */
    public function __construct(
        public string $name,
        public array  $dependsOn = [],
    ) {}
}
