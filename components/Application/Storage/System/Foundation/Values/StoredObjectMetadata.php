<?php

declare(strict_types=1);

namespace Avax\Components\Application\Storage\System\Foundation\Values;

final readonly class StoredObjectMetadata
{
    public function __construct(
        public int     $size,
        public ?int    $lastModified = null,
        public ?string $mimeType = null,
        public ?string $visibility = null,
    ) {}

    /**
     * @return array{size: int, last_modified: ?int, mime_type: ?string, visibility: ?string}
     */
    public function toArray() : array
    {
        return [
            'size'          => $this->size,
            'last_modified' => $this->lastModified,
            'mime_type'     => $this->mimeType,
            'visibility'    => $this->visibility,
        ];
    }
}