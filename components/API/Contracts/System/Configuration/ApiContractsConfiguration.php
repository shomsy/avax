<?php

declare(strict_types=1);

namespace Avax\Components\API\Contracts\System\Configuration;

final readonly class ApiContractsConfiguration
{
    /**
     * @param list<string> $deprecatedVersions
     * @param list<string> $breakingChangeRules
     */
    public function __construct(
        public string $currentVersion = '1.0.0',
        public bool   $strictMode = false,
        public array  $deprecatedVersions = [],
        public array  $breakingChangeRules = [],
    ) {}

    public static function make() : self
    {
        return new self();
    }

    public function withVersion(string $version) : self
    {
        return new self(
            currentVersion     : $version,
            strictMode         : $this->strictMode,
            deprecatedVersions : $this->deprecatedVersions,
            breakingChangeRules: $this->breakingChangeRules,
        );
    }

    public function withStrictMode(bool $strict) : self
    {
        return new self(
            currentVersion     : $this->currentVersion,
            strictMode         : $strict,
            deprecatedVersions : $this->deprecatedVersions,
            breakingChangeRules: $this->breakingChangeRules,
        );
    }
}
