<?php

declare(strict_types=1);

namespace Avax\Filesystem\Storage;

use Exception;

class FileNotFoundException extends Exception
{
    public function __construct(string $string)
    {
        parent::__construct(message: $string);
    }
}