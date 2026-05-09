<?php

declare(strict_types=1);

namespace Avax\Components\Application\Filesystem\System\Foundation\Values;

final readonly class DirectoryListing
{
    /**
     * @param list<string> $files
     * @param list<string> $directories
     */
    public function __construct(
        public array $files,
        public array $directories,
    ) {}

    public function isEmpty() : bool
    {
        return empty($this->files) && empty($this->directories);
    }

    public function count() : int
    {
        return count($this->files) + count($this->directories);
    }

    /**
     * @return list<string>
     */
    public function all() : array
    {
        return [...$this->files, ...$this->directories];
    }
}