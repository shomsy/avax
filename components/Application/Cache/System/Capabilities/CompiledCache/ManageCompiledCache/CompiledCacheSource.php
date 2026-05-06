<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\CompiledCache\ManageCompiledCache;

use InvalidArgumentException;

final readonly class CompiledCacheSource
{
    public function __construct(
        public string $path,
        public int $mtime,
        public ?string $checksum = null,
    ) {
        $this->validate();
    }

    private function validate(): void
    {
        if ($this->path === '') {
            throw new InvalidArgumentException(message: 'Source path cannot be empty');
        }

        if (! file_exists($this->path)) {
            throw new InvalidArgumentException(message: sprintf('Source file does not exist: %s', $this->path));
        }

        if ($this->mtime < 0) {
            throw new InvalidArgumentException(message: 'Source mtime cannot be negative');
        }
    }

    public static function fromPath(string $path): self
    {
        if (! file_exists($path)) {
            throw new InvalidArgumentException(message: sprintf('Source file does not exist: %s', $path));
        }

        $stat = stat($path);
        $mtime = $stat !== false ? $stat['mtime'] : filemtime($path);

        if (! is_int($mtime)) {
            throw new InvalidArgumentException(message: sprintf('Cannot read source mtime: %s', $path));
        }

        return new self(
            path    : $path,
            mtime   : $mtime,
        );
    }

    public function fingerprint(): string
    {
        if ($this->checksum !== null) {
            return $this->checksum;
        }

        // Fast fingerprint based on mtime and size
        // md5_file is too slow for per-request checks
        return md5($this->path.':'.$this->mtime.':'.filesize($this->path));
    }
}
