<?php

declare(strict_types=1);

namespace components\Database\System\Capabilities\Query\Grammar;

use InvalidArgumentException;

final class DialectFactory
{
    public function make(string|Dialect $dialect): GrammarInterface
    {
        return self::grammarFor(dialect: $dialect);
    }

    public static function grammarFor(string|Dialect $dialect): GrammarInterface
    {
        return self::resolve(dialect: $dialect)->grammar();
    }

    public static function resolve(string|Dialect $dialect): Dialect
    {
        if ($dialect instanceof Dialect) {
            return $dialect;
        }

        return Dialect::tryFrom(value: strtolower(string: $dialect))
            ?? throw new InvalidArgumentException(message: "Unsupported database dialect: {$dialect}");
    }
}
