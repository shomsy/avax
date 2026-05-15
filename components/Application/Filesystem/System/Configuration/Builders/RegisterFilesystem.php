<?php

declare(strict_types=1);

namespace Avax\Components\Application\Filesystem\System\Configuration\Builders;

use Avax\Components\Application\Filesystem\System\Configuration\FilesystemConfiguration;
use Avax\Components\Application\Filesystem\System\PublicSurface\Filesystem;

final readonly class RegisterFilesystem
{
    public function __construct(
        private FilesystemConfiguration $config,
    ) {}

    /**
     * @param array<string, mixed> $config
     */
    public static function fromArray(array $config) : self
    {
        return new self(
            config: FilesystemConfiguration::fromArray($config),
        );
    }

    public function execute() : Filesystem
    {
        $root = $this->config->root;

        if ($root !== '') {
            return new Filesystem();
        }

        return new Filesystem();
    }
}
