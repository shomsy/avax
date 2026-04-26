<?php

declare(strict_types=1);

namespace Avax\DataFoundation\DataTransfer\Foundation;

use Stringable;

final readonly class FieldPath implements Stringable
{
    /**
     * @param list<string> $segments
     */
    private function __construct(private array $segments) {}

    public static function from(string $path) : self
    {
        if ($path === '') {
            return self::root();
        }

        return new self(segments: explode(separator: '.', string: $path));
    }

    public static function root() : self
    {
        return new self(segments: []);
    }

    public function append(string|int $segment) : self
    {
        return new self(segments: [...$this->segments, (string) $segment]);
    }

    public function isRoot() : bool
    {
        return $this->segments === [];
    }

    public function __toString() : string
    {
        return $this->segments === [] ? '$' : implode(separator: '.', array: $this->segments);
    }
}
