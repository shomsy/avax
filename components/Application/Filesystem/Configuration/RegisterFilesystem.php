<?php

declare(strict_types=1);

namespace Avax\Components\Application\Filesystem\Configuration;

use Avax\Components\Application\Filesystem\System\PublicSurface\Filesystem;

final readonly class RegisterFilesystem
{
    public function __construct(
        private FilesystemConfiguration $config,
    ) {}

    public static function fromArray(array $config) : self
    {
        return new self(
            config: FilesystemConfiguration::fromArray($config),
        );
    }

    public function execute() : Filesystem
    {
        $root = $this->config->system->root;

        if ($root !== '') {
            return new Filesystem();
        }

        return new Filesystem();
    }
}