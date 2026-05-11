<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\ConfigExplanation;

/**
 * Explains where a config value comes from.
 */
final readonly class ConfigSource
{
    public const string SOURCE_FILE = 'file';

    public const string SOURCE_ENV = 'env';

    public const string SOURCE_OVERRIDE = 'override';

    public const string SOURCE_DEFAULT = 'default';

    public function __construct(
        public string $key,
        public string $source,
        public mixed $value,
        public string|null $envVar = null,
        public string|null $filePath = null,
    ) {
    }

    public function isFromEnv(): bool
    {
        return $this->source === self::SOURCE_ENV;
    }

    public function isFromFile(): bool
    {
        return $this->source === self::SOURCE_FILE;
    }
}
