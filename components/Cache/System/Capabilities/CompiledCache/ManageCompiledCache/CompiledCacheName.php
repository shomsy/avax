<?php

declare(strict_types=1);

namespace Avax\Cache\System\Capabilities\CompiledCache\ManageCompiledCache;

use InvalidArgumentException;

final class CompiledCacheName
{
    private const MAX_LENGTH    = 128;
    private const VALID_PATTERN = '/^[a-zA-Z0-9][a-zA-Z0-9._-]*$/';

    public function __construct(
        public readonly string $name
    )
    {
        $this->validate();
    }

    private function validate() : void
    {
        if ($this->name === '') {
            throw new InvalidArgumentException(message: 'Compiled cache name cannot be empty');
        }

        if (strlen($this->name) > self::MAX_LENGTH) {
            throw new InvalidArgumentException(message: sprintf(
                                                            'Compiled cache name must not exceed %d characters',
                                                            self::MAX_LENGTH
                                                        ));
        }

        if (str_contains($this->name, '/') || str_contains($this->name, '\\')) {
            throw new InvalidArgumentException(
                message: 'Compiled cache name cannot contain forward or backward slashes'
            );
        }

        if (str_contains($this->name, '..')) {
            throw new InvalidArgumentException(
                message: 'Compiled cache name cannot contain parent directory traversal'
            );
        }

        if (! preg_match(self::VALID_PATTERN, $this->name)) {
            throw new InvalidArgumentException(message: sprintf(
                                                            'Compiled cache name "%s" contains invalid characters',
                                                            $this->name
                                                        ));
        }
    }

    public static function fromString(string $name) : self
    {
        return new self(name: $name);
    }

    public function toString() : string
    {
        return $this->name;
    }
}