<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\ConfigValidation;

/**
 * Represents a single field in a config schema.
 */
final readonly class ConfigSchemaField
{
    public const string TYPE_STRING = 'string';

    public const string TYPE_INT = 'int';

    public const string TYPE_FLOAT = 'float';

    public const string TYPE_BOOL = 'bool';

    public const string TYPE_ARRAY = 'array';

    public const string TYPE_NON_EMPTY_STRING = 'non-empty-string';

    public const string TYPE_URL = 'url';

    public const string TYPE_EMAIL = 'email';

    /**
     * @param list<mixed> $allowed
     */
    public function __construct(
        public string $name,
        public string $type = self::TYPE_STRING,
        public bool $required = true,
        public mixed $default = null,
        public array $allowed = [],
        public ?string $description = null,
    ) {
    }
}
