<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\ORM\Attributes;

use Attribute;

#[Attribute(flags: Attribute::TARGET_PROPERTY)]
final readonly class ManyToOne
{
    /**
     * @param list<string> $cascade
     */
    public function __construct(
        public string $targetEntity,
        public array $cascade = [],
        public bool $lazy = true,
    ) {}
}
