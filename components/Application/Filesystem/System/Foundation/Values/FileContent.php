<?php

declare(strict_types=1);

namespace Avax\Components\Application\Filesystem\System\Foundation\Values;

final readonly class FileContent
{
    public function __construct(
        public string $content,
    ) {}

    public function isEmpty() : bool
    {
        return $this->content === '';
    }

    public function length() : int
    {
        return strlen($this->content);
    }
}