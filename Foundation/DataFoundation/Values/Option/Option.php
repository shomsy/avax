<?php

declare(strict_types=1);

namespace Avax\DataFoundation\Values\Option;

/**
 * Optional value that is either present or absent.
 */
abstract readonly class Option
{
    public static function fromNullable(mixed $value) : self
    {
        return $value === null ? self::none() : self::some(value: $value);
    }

    public static function none() : self
    {
        return None::instance();
    }

    public static function some(mixed $value) : self
    {
        return new Some(value: $value);
    }

    final public function isNone() : bool
    {
        return ! $this->isSome();
    }

    abstract public function isSome() : bool;

    final public function unwrapOr(mixed $default) : mixed
    {
        return $this->isSome() ? $this->unwrap() : $default;
    }

    abstract public function unwrap() : mixed;

    final public function map(callable $callback) : self
    {
        return $this->isSome()
            ? self::some(value: $callback($this->unwrap()))
            : self::none();
    }

    final public function match(callable $some, callable $none) : mixed
    {
        return $this->isSome()
            ? $some($this->unwrap())
            : $none();
    }

    final public function toNullable() : mixed
    {
        return $this->isSome() ? $this->unwrap() : null;
    }
}
