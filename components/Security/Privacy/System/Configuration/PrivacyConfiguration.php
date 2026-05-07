<?php

declare(strict_types=1);

namespace Avax\Components\Security\Privacy\System\Configuration;

final readonly class PrivacyConfiguration
{
    public function __construct(
        public int    $retentionDays = 365,
        public string $exportFormat = 'json',
        public bool   $anonymizeOnDelete = true,
    ) {}

    public static function make() : self
    {
        return new self();
    }
}
