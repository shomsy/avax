<?php

declare(strict_types=1);

namespace Avax\Components\Data\System\Capabilities\Collections\Internal\Composites;

/**
 * A single field in a Record.
 */
final readonly class RecordField
{
    public function __construct(private string $name, private mixed $value) {}

    public function name() : string { return $this->name; }

    public function value() : mixed { return $this->value; }
}
