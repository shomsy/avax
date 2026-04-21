<?php

declare(strict_types=1);

namespace Avax\Filesystem\Storage;

use Exception;
use Override;

class FileNotFoundException extends Exception
{
    #[Override]
    public function __construct(string $string)
    {
        parent::__construct(message: $string);
    }
}
