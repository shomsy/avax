<?php

declare(strict_types=1);

namespace Avax\Components\Application\Storage\System\Foundation\Values;

final readonly class StoredObject
{
    public function __construct(
        public DiskName              $disk,
        public StoragePath           $path,
        public string|null               $content = null,
        public StoredObjectMetadata|null $metadata = null,
    ) {}

    public function exists() : bool
    {
        return $this->content !== null;
    }
}