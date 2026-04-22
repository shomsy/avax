<?php

declare(strict_types=1);

namespace Avax\Database\System\Capabilities\Migrations\Design\Enums;

enum FieldType: string
{
    case Id               = 'id';
    case TinyInteger      = 'tinyInteger';
    case SmallInteger     = 'smallInteger';
    case Integer          = 'integer';
    case BigInteger       = 'bigInteger';
    case Decimal          = 'decimal';
    case Float            = 'float';
    case Double           = 'double';
    case Boolean          = 'boolean';
    case MediumInteger    = 'mediumInteger';
    case Serial           = 'serial';
    case BigSerial        = 'bigSerial';
    case Real             = 'real';
    case String           = 'string';
    case Char             = 'char';
    case Text             = 'text';
    case MediumText       = 'mediumText';
    case LongText         = 'longText';
    case TinyText         = 'tinyText';
    case Nchar            = 'nchar';
    case Nvarchar         = 'nvarchar';
    case Ntext            = 'ntext';
    case Binary           = 'binary';
    case Uuid             = 'uuid';
    case UuidNative       = 'uuidNative';
    case Varbinary        = 'varbinary';
    case Blob             = 'blob';
    case TinyBlob         = 'tinyBlob';
    case MediumBlob       = 'mediumBlob';
    case LongBlob         = 'longBlob';
    case Bytea            = 'bytea';
    case Bit              = 'bit';
    case Date             = 'date';
    case Datetime         = 'datetime';
    case Timestamp        = 'timestamp';
    case Time             = 'time';
    case Year             = 'year';
    case Interval         = 'interval';
    case Timestamps       = 'timestamps';
    case SoftDeletes      = 'softDeletes';
    case Json             = 'json';
    case Jsonb            = 'jsonb';
    case Enum             = 'enum';
    case Set              = 'set';
    case Xml              = 'xml';
    case Point            = 'point';
    case LineString       = 'lineString';
    case Polygon          = 'polygon';
    case Geometry         = 'geometry';
    case Geography        = 'geography';
    case Inet             = 'inet';
    case Cidr             = 'cidr';
    case Macaddr          = 'macaddr';
    case Tsvector         = 'tsvector';
    case Tsquery          = 'tsquery';
    case Money            = 'money';
    case SmallMoney       = 'smallMoney';
    case UniqueIdentifier = 'uniqueIdentifier';
    case RowVersion       = 'rowVersion';

    public static function fromInput(string|self|null $value) : self|null
    {
        if ($value instanceof self) {
            return $value;
        }

        if ($value === null) {
            return null;
        }

        return match (self::normalize(value: $value)) {
            'id'                                           => self::Id,
            'tinyinteger', 'tiny_integer', 'tinyint'       => self::TinyInteger,
            'smallinteger', 'small_integer', 'smallint'    => self::SmallInteger,
            'integer', 'int'                               => self::Integer,
            'biginteger', 'big_integer', 'bigint'          => self::BigInteger,
            'decimal'                                      => self::Decimal,
            'float'                                        => self::Float,
            'double'                                       => self::Double,
            'boolean', 'bool'                              => self::Boolean,
            'mediuminteger', 'medium_integer', 'mediumint' => self::MediumInteger,
            'serial'                                       => self::Serial,
            'bigserial', 'big_serial'                      => self::BigSerial,
            'real'                                         => self::Real,
            'string', 'varchar'                            => self::String,
            'char'                                         => self::Char,
            'text'                                         => self::Text,
            'mediumtext', 'medium_text'                    => self::MediumText,
            'longtext', 'long_text'                        => self::LongText,
            'tinytext', 'tiny_text'                        => self::TinyText,
            'nchar'                                        => self::Nchar,
            'nvarchar'                                     => self::Nvarchar,
            'ntext'                                        => self::Ntext,
            'binary'                                       => self::Binary,
            'uuid'                                         => self::Uuid,
            'uuidnative', 'uuid_native'                    => self::UuidNative,
            'varbinary', 'var_binary'                      => self::Varbinary,
            'blob'                                         => self::Blob,
            'tinyblob', 'tiny_blob'                        => self::TinyBlob,
            'mediumblob', 'medium_blob'                    => self::MediumBlob,
            'longblob', 'long_blob'                        => self::LongBlob,
            'bytea'                                        => self::Bytea,
            'bit'                                          => self::Bit,
            'date'                                         => self::Date,
            'datetime', 'date_time'                        => self::Datetime,
            'timestamp'                                    => self::Timestamp,
            'time'                                         => self::Time,
            'year'                                         => self::Year,
            'interval'                                     => self::Interval,
            'timestamps'                                   => self::Timestamps,
            'softdeletes', 'soft_deletes'                  => self::SoftDeletes,
            'json'                                         => self::Json,
            'jsonb'                                        => self::Jsonb,
            'enum'                                         => self::Enum,
            'set'                                          => self::Set,
            'xml'                                          => self::Xml,
            'point'                                        => self::Point,
            'linestring', 'line_string'                    => self::LineString,
            'polygon'                                      => self::Polygon,
            'geometry'                                     => self::Geometry,
            'geography'                                    => self::Geography,
            'inet'                                         => self::Inet,
            'cidr'                                         => self::Cidr,
            'macaddr', 'mac_addr'                          => self::Macaddr,
            'tsvector', 'ts_vector'                        => self::Tsvector,
            'tsquery', 'ts_query'                          => self::Tsquery,
            'money'                                        => self::Money,
            'smallmoney', 'small_money'                    => self::SmallMoney,
            'uniqueidentifier', 'unique_identifier'        => self::UniqueIdentifier,
            'rowversion', 'row_version'                    => self::RowVersion,
            default                                        => null,
        };
    }

    private static function normalize(string $value) : string
    {
        $value = preg_replace('/(?<!^)[A-Z]/', '_$0', $value) ?? $value;
        $value = str_replace(['-', ' '], '_', $value);

        return strtolower($value);
    }
}
