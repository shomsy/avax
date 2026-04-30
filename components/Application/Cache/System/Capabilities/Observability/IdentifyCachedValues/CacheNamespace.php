<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\Observability\IdentifyCachedValues;

use InvalidArgumentException;
use Override;
use Stringable;

final readonly class CacheNamespace implements Stringable
{
    private const int MAX_LENGTH = 128;

    private const string VALID_PATTERN = '/^[a-zA-Z0-9_\-]+$/';

    public function __construct(
        public string $name,
    )
    {
        $this->validate(name: $name);
    }

    private function validate(string $name) : void
    {
        $normalized = trim($name);
        $length     = strlen($normalized);

        if ($length === 0) {
            throw new InvalidArgumentException(message: 'Namespace cannot be empty');
        }

        if ($length > self::MAX_LENGTH) {
            throw new InvalidArgumentException(
                message: sprintf('Namespace must not exceed %d characters', self::MAX_LENGTH),
            );
        }

        if (in_array(preg_match(self::VALID_PATTERN, $normalized), [0, false], true)) {
            throw new InvalidArgumentException(
                message: 'Namespace contains invalid characters. Only alphanumeric, underscore, and dash are allowed',
            );
        }
    }

    public static function create(string $name) : self
    {
        return new self(name: $name);
    }

    public static function fromKey(CacheKey $cacheKey) : self
    {
        $keyNamespace = $cacheKey->namespace;

        if ($keyNamespace === null) {
            throw new InvalidArgumentException(message: 'System key does not have a namespace');
        }

        return new self(name: $keyNamespace);
    }

    #[Override]
    public function __toString() : string
    {
        return $this->name;
    }

    public function toString() : string
    {
        return $this->name;
    }
}
