<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\CompiledCache\ManageCompiledCache;

use InvalidArgumentException;

final readonly class CompiledCacheName
{
    private const int MAX_LENGTH = 128;

    private const string VALID_PATTERN = '/^[a-zA-Z0-9][a-zA-Z0-9._-]*$/';

    public function __construct(
        public string $name,
    ) {
        $this->validate();
    }

    /**
 * @throws InvalidArgumentException
 */
private function validate(): void
    {
        if ($this->name === '') {
            throw new InvalidArgumentException(message: 'Compiled cache name cannot be empty');
        }

        if (strlen($this->name) > self::MAX_LENGTH) {
            throw new InvalidArgumentException(message: sprintf(
                'Compiled cache name must not exceed %d characters',
                self::MAX_LENGTH,
            ));
        }

        if (str_contains($this->name, '/') || str_contains($this->name, '\\')) {
            throw new InvalidArgumentException(
                message: 'Compiled cache name cannot contain forward or backward slashes',
            );
        }

        if (str_contains($this->name, '..')) {
            throw new InvalidArgumentException(
                message: 'Compiled cache name cannot contain parent directory traversal',
            );
        }

        if (in_array(preg_match(self::VALID_PATTERN, $this->name), [0, false], true)) {
            throw new InvalidArgumentException(message: sprintf(
                'Compiled cache name "%s" contains invalid characters',
                $this->name,
            ));
        }
    }

    public static function fromString(string $name): self
    {
        return new self(name: $name);
    }

    public function toString(): string
    {
        return $this->name;
    }
}
