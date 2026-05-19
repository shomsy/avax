<?php

declare(strict_types=1);

namespace Avax\Database\System\Capabilities\ORM\Attributes;

use Attribute;

#[Attribute(flags: Attribute::TARGET_PROPERTY)]
final readonly class ManyToMany
{
    /**
     * @param list<string> $cascade
     */
    public function __construct(
        public string  $targetEntity,
        public ?string $mappedBy = null,
        public ?string $inversedBy = null,
        public ?string $joinTable = null,
        public array   $cascade = [],
        public bool    $lazy = true
    ) {}
}
