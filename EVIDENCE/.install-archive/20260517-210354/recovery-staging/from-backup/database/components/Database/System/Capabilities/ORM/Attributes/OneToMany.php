<?php

declare(strict_types=1);

namespace Avax\Database\System\Capabilities\ORM\Attributes;

use Attribute;

#[Attribute(flags: Attribute::TARGET_PROPERTY)]
final readonly class OneToMany
{
    /**
     * @param  list<string>  $cascade
     */
    public function __construct(
        public string $targetEntity,
        public string $mappedBy,
        public array $cascade = [],
        public bool $lazy = true
    ) {
    }
}
