<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\ORM\Metadata;

use Avax\Components\DataStack\Database\System\Capabilities\ORM\Relations\RelationKind;

final readonly class RelationMetadata
{
    /**
     * @param list<string> $cascade
     */
    public function __construct(
        public string      $property,
        public RelationKind $relationKind,
        public string      $targetEntity,
        public string|null $mappedBy = null,
        public string|null $inversedBy = null,
        public string|null $joinColumn = null,
        public string      $referencedColumn = 'id',
        public array       $cascade = [],
        public bool        $lazy = true,
    ) {}
}
