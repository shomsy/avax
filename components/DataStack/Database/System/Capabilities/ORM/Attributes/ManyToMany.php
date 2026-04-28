<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\ORM\Attributes;

use Attribute;

#[Attribute(flags: Attribute::TARGET_PROPERTY)]
final readonly class ManyToMany
{
    /**
     * @param list<string> $cascade
     */
    public function __construct(
        public string      $targetEntity,
        public string|null $mappedBy = null,
        public string|null $inversedBy = null,
        public string|null $joinTable = null,
        public array       $cascade = [],
        public bool        $lazy = true
    ) {}
}
