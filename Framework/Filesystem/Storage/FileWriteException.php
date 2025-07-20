<?php

declare(strict_types=1);

namespace Gemini\Filesystem\Storage;

use Exception;

class FileWriteException extends Exception
{
    public function __construct(string $string)
    {
        parent::__construct($string);
    }
}