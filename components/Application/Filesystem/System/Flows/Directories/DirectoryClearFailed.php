<?php

declare(strict_types=1);

namespace Avax\Components\Application\Filesystem\System\Flows\Directories;

use RuntimeException;

class DirectoryClearFailed extends RuntimeException
{
    public function __construct(string $path)
    {
        parent::__construct(message: 'Failed to clear directory: ' . $path);
    }
}
