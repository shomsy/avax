<?php

declare(strict_types=1);

namespace Avax\Components\Application\Filesystem\System\Flows\Directories;

use RuntimeException;

class DirectoryCreateFailed extends RuntimeException
{
    public function __construct(string $path)
    {
        parent::__construct(message: 'Failed to create directory: '.$path);
    }
}
