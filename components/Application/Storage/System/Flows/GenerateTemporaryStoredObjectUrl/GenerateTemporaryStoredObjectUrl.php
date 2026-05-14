<?php

declare(strict_types=1);

namespace Avax\Components\Application\Storage\System\Flows\GenerateTemporaryStoredObjectUrl;

use Avax\Components\Application\Storage\System\Capabilities\Disks\Disk;
use Avax\Components\Application\Storage\System\Foundation\Failure\TemporaryUrlNotSupported;
use Avax\Components\Application\Storage\System\Foundation\Values\StoragePath;
use DateTimeInterface;

final readonly class GenerateTemporaryStoredObjectUrl
{
    public function __construct(
        private Disk $disk,
    ) {}

    /**
 * @throws TemporaryUrlNotSupported
 */
public function execute(string $path, DateTimeInterface $expires) : string
    {
        if (! $this->disk->supportsTemporaryUrl()) {
            throw new TemporaryUrlNotSupported('local');
        }

        return $this->disk->temporaryUrl(new StoragePath($path), $expires);
    }
}