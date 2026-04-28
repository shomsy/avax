<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Capabilities\Collections\Internal\Values\Result;

/**
 * Operation result that is either successful or failed.
 */
abstract class Result
{
    final public function map(callable $callback) : self
    {
        return $this->isOk()
            ? self::ok(value: $callback($this->unwrap()))
            : $this;
    }

    abstract public function isOk() : bool;

    public static function ok(mixed $value) : self
    {
        return new Success(value: $value);
    }

    abstract public function unwrap() : mixed;

    final public function mapError(callable $callback) : self
    {
        return $this->isError()
            ? self::error(error: $callback($this->unwrapError()))
            : $this;
    }

    final public function isError() : bool
    {
        return ! $this->isOk();
    }

    public static function error(mixed $error) : self
    {
        return new Failure(error: $error);
    }

    abstract public function unwrapError() : mixed;

    final public function match(callable $ok, callable $error) : mixed
    {
        return $this->isOk()
            ? $ok($this->unwrap())
            : $error($this->unwrapError());
    }
}
