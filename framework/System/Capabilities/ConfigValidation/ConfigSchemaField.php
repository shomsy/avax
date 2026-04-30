<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\ConfigValidation;

/**
 * Represents a single field in a config schema.
 */
final readonly class ConfigSchemaField
{
    public const TYPE_STRING           = 'string';
    public const TYPE_INT              = 'int';
    public const TYPE_FLOAT            = 'float';
    public const TYPE_BOOL             = 'bool';
    public const TYPE_ARRAY            = 'array';
    public const TYPE_NON_EMPTY_STRING = 'non-empty-string';
    public const TYPE_URL              = 'url';
    public const TYPE_EMAIL            = 'email';

    public function __construct(
        public string      $name,
        public string      $type = 'string',
        public bool        $required = true,
        public mixed       $default = null,
        public array       $allowed = [],
        public string|null $description = null,
    ) {}
}
