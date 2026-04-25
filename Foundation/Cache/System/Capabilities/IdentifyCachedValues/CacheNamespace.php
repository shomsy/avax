<?php

declare(strict_types=1);

namespace Avax\Cache\System\Capabilities\IdentifyCachedValues;

use InvalidArgumentException;
use Stringable;

final readonly class CacheNamespace implements Stringable
{
    private const MAX_LENGTH    = 128;
    private const VALID_PATTERN = '/^[a-zA-Z0-9_\-]+$/';

    public function __construct(
        public string $name
    )
    {
        $this->validate($name);
    }

    private function validate(string $name) : void
    {
        $normalized = trim($name);
        $length     = strlen($normalized);

        if ($length === 0) {
            throw new InvalidArgumentException('Namespace cannot be empty');
        }

        if ($length > self::MAX_LENGTH) {
            throw new InvalidArgumentException(
                sprintf('Namespace must not exceed %d characters', self::MAX_LENGTH)
            );
        }

        if (! preg_match(self::VALID_PATTERN, $normalized)) {
            throw new InvalidArgumentException(
                'Namespace contains invalid characters. Only alphanumeric, underscore, and dash are allowed'
            );
        }
    }

    public static function create(string $name) : self
    {
        return new self($name);
    }

    public static function fromKey(CacheKey $key) : self
    {
        $keyNamespace = $key->namespace;

        if ($keyNamespace === null) {
            throw new InvalidArgumentException('System key does not have a namespace');
        }

        return new self($keyNamespace);
    }

    public function __toString() : string
    {
        return $this->toString();
    }

    public function toString() : string
    {
        return $this->name;
    }
}