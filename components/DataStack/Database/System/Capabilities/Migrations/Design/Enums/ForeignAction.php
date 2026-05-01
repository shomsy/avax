<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\Migrations\Design\Enums;

enum ForeignAction: string
{
    case Cascade = 'CASCADE';
    case Restrict = 'RESTRICT';
    case NoAction = 'NO ACTION';
    case SetNull = 'SET NULL';
    case SetDefault = 'SET DEFAULT';

    public static function fromInput(string|self|null $value): ?self
    {
        if ($value instanceof self) {
            return $value;
        }

        if ($value === null) {
            return null;
        }

        return match (self::normalize(value: $value)) {
            'CASCADE' => self::Cascade,
            'RESTRICT' => self::Restrict,
            'NO ACTION' => self::NoAction,
            'SET NULL' => self::SetNull,
            'SET DEFAULT' => self::SetDefault,
            default => null,
        };
    }

    private static function normalize(string $value): string
    {
        $value = preg_replace(pattern: '/(?<!^)[A-Z]/', replacement: '_$0', subject: $value) ?? $value;
        $value = str_replace(search: ['-', '_'], replace: ' ', subject: $value);

        return strtoupper(string: trim(string: $value));
    }
}
