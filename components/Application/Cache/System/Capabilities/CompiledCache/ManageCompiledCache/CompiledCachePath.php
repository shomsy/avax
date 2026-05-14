<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\CompiledCache\ManageCompiledCache;

use InvalidArgumentException;
use Random\RandomException;

final readonly class CompiledCachePath
{
    public function __construct(
        public string $path,
    ) {
        $this->validate();
    }

    /**
 * @throws InvalidArgumentException
 */
private function validate(): void
    {
        if ($this->path === '') {
            throw new InvalidArgumentException(message: 'Compiled cache path cannot be empty');
        }
    }

    public function toString(): string
    {
        return $this->path;
    }

    /**
     * @throws RandomException
     */
    public function toTemporaryPath(): string
    {
        return $this->path.'.'.bin2hex(random_bytes(8)).'.tmp';
    }

    public function directory(): string
    {
        return dirname($this->path);
    }

    public function filename(): string
    {
        return basename($this->path);
    }
}
