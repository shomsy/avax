<?php

declare(strict_types=1);

namespace Avax\Database\System\Capabilities\Migrations\Design\Enums;

enum FieldModifier: string
{
    case Nullable           = 'nullable';
    case Default            = 'default';
    case AutoIncrement      = 'auto_increment';
    case Unsigned           = 'unsigned';
    case Primary            = 'primary';
    case Unique             = 'unique';
    case Index              = 'index';
    case Charset            = 'charset';
    case Collation          = 'collation';
    case UseCurrent         = 'use_current';
    case UseCurrentOnUpdate = 'on_update_current';
    case StoredAs           = 'stored_as';
    case VirtualAs          = 'virtual_as';
    case Foreign            = 'foreign';
    case Comment            = 'comment';

    public static function fromInput(string|self|null $value) : self|null
    {
        if ($value instanceof self) {
            return $value;
        }

        if ($value === null) {
            return null;
        }

        return match (self::normalize(value: $value)) {
            'nullable'                                                         => self::Nullable,
            'default'                                                          => self::Default,
            'autoincrement', 'auto_increment'                                  => self::AutoIncrement,
            'unsigned'                                                         => self::Unsigned,
            'primary'                                                          => self::Primary,
            'unique'                                                           => self::Unique,
            'index'                                                            => self::Index,
            'charset'                                                          => self::Charset,
            'collation'                                                        => self::Collation,
            'usecurrent', 'use_current', 'current'                             => self::UseCurrent,
            'usecurrentonupdate', 'use_current_on_update', 'on_update_current' => self::UseCurrentOnUpdate,
            'storedas', 'stored_as'                                            => self::StoredAs,
            'virtualas', 'virtual_as'                                          => self::VirtualAs,
            'foreign', 'references'                                            => self::Foreign,
            'comment'                                                          => self::Comment,
            default                                                            => null,
        };
    }

    private static function normalize(string $value) : string
    {
        $value = preg_replace(pattern: '/(?<!^)[A-Z]/', replacement: '_$0', subject: $value) ?? $value;
        $value = str_replace(search: ['-', ' '], replace: '_', subject: $value);

        return strtolower(string: $value);
    }
}
