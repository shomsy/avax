<?php

declare(strict_types=1);

namespace Avax\Filesystem\Storage;

use Exception;

class FileWriteException extends Exception
{
    #[\Override]
    public function __construct(string $string)
    {
        parent::__construct(message: $string);
    }
}