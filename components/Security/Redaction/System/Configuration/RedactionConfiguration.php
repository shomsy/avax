<?php

declare(strict_types=1);

namespace Avax\Components\Security\Redaction\System\Configuration;

final readonly class RedactionConfiguration
{
    public function __construct(
        public string $redactionMask = '***',
        public bool   $strictMode = false,
        /** @var list<string> */
        public array  $customPatterns = [],
    ) {}

    public static function make() : self
    {
        return new self();
    }
}
