<?php

declare(strict_types=1);

namespace Avax\Components\Application\Text\System\Configuration;

final readonly class TextConfiguration
{
    public function __construct(
        public string $defaultEncoding = 'UTF-8',
        public int    $defaultMaxLength = 255,
        public bool   $strictEmailValidation = true,
        public bool   $strictUrlValidation = true,
        public string $slugSeparator = '-',
        public bool   $slugLowercase = true,
    ) {}
}
