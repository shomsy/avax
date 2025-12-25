<?php

declare(strict_types=1);

namespace Avax\Migrations\Design\TypeMapping;

/**
 * Enterprise-grade SQL to PHP type mapper for DTOs, Entities, and Value Objects.
 *
 * -- intent: provide accurate type mapping for code generation and ORM integration.
 */
final class SQLToPHPTypeMapper
{
    /**
     * Complete mapping of SQL types to PHP native types.
     *
     * @var array<string, string>
     */
    private const TYPE_MAP
        = [
            // NUMERIC TYPES
            'TINYINT'          => 'int',
            'SMALLINT'         => 'int',
            'MEDIUMINT'        => 'int',
            'INT'              => 'int',
            'INTEGER'          => 'int',
            'BIGINT'           => 'int',
            'SERIAL'           => 'int',
            'BIGSERIAL'        => 'int',
            'DECIMAL'          => 'string',
            'NUMERIC'          => 'string',
            'FLOAT'            => 'float',
            'REAL'             => 'float',
            'DOUBLE'           => 'float',
            'BOOLEAN'          => 'bool',
            'BOOL'             => 'bool',
            'BIT'              => 'int',

            // STRING TYPES
            'CHAR'             => 'string',
            'VARCHAR'          => 'string',
            'TEXT'             => 'string',
            'TINYTEXT'         => 'string',
            'MEDIUMTEXT'       => 'string',
            'LONGTEXT'         => 'string',
            'NCHAR'            => 'string',
            'NVARCHAR'         => 'string',
            'NTEXT'            => 'string',

            // BINARY TYPES
            'BINARY'           => 'string',
            'VARBINARY'        => 'string',
            'BLOB'             => 'string',
            'TINYBLOB'         => 'string',
            'MEDIUMBLOB'       => 'string',
            'LONGBLOB'         => 'string',
            'BYTEA'            => 'string',

            // DATE/TIME TYPES
            'DATE'             => 'DateTimeImmutable',
            'DATETIME'         => 'DateTimeImmutable',
            'TIMESTAMP'        => 'DateTimeImmutable',
            'TIME'             => 'DateTimeImmutable',
            'YEAR'             => 'int',
            'INTERVAL'         => 'DateInterval',

            // JSON TYPES
            'JSON'             => 'array',
            'JSONB'            => 'array',

            // SPECIAL TYPES
            'ENUM'             => 'string',
            'SET'              => 'array',
            'UUID'             => 'string',
            'XML'              => 'string',

            // GIS / SPATIAL TYPES
            'POINT'            => 'array',
            'LINESTRING'       => 'array',
            'POLYGON'          => 'array',
            'GEOMETRY'         => 'array',
            'GEOGRAPHY'        => 'array',

            // POSTGRESQL SPECIFIC
            'INET'             => 'string',
            'CIDR'             => 'string',
            'MACADDR'          => 'string',
            'TSVECTOR'         => 'string',
            'TSQUERY'          => 'string',

            // SQL SERVER SPECIFIC
            'MONEY'            => 'string',
            'SMALLMONEY'       => 'string',
            'UNIQUEIDENTIFIER' => 'string',
            'ROWVERSION'       => 'string',
        ];

    /**
     * Map SQL type to PHP DocBlock type hint.
     *
     * @param string $sqlType
     * @param bool   $nullable
     *
     * @return string
     */
    public function toDocBlockType(string $sqlType, bool $nullable = false) : string
    {
        $phpType = $this->toPhpType(sqlType: $sqlType);

        $enhancedType = match ($phpType) {
            'array' => $this->getArrayDocType(sqlType: $sqlType),
            default => $phpType,
        };

        return $nullable ? "{$enhancedType}|null" : $enhancedType;
    }

    /**
     * Map SQL type to PHP native type.
     *
     * @param string $sqlType
     *
     * @return string
     */
    public function toPhpType(string $sqlType) : string
    {
        $baseType = $this->extractBaseType(sqlType: $sqlType);

        return self::TYPE_MAP[$baseType] ?? 'mixed';
    }

    private function extractBaseType(string $sqlType) : string
    {
        $baseType = preg_replace(pattern: '/[\(\s].*/', replacement: '', subject: $sqlType);

        return strtoupper(string: trim(string: $baseType));
    }

    private function getArrayDocType(string $sqlType) : string
    {
        $baseType = $this->extractBaseType(sqlType: $sqlType);

        return match ($baseType) {
            'JSON', 'JSONB' => 'array<string, mixed>',
            'SET'           => 'array<int, string>',
            'POINT'         => 'array{x: float, y: float}',
            'LINESTRING'    => 'array<int, array{x: float, y: float}>',
            'POLYGON'       => 'array<int, array<int, array{x: float, y: float}>>',
            default         => 'array',
        };
    }

    /**
     * @param string $sqlType
     *
     * @return string|null
     */
    public function suggestValueObject(string $sqlType) : ?string
    {
        if (! $this->shouldUseValueObject(sqlType: $sqlType)) {
            return null;
        }

        $baseType = $this->extractBaseType(sqlType: $sqlType);

        return match ($baseType) {
            'UUID'       => 'Uuid',
            'INET'       => 'IpAddress',
            'CIDR'       => 'NetworkRange',
            'MACADDR'    => 'MacAddress',
            'MONEY'      => 'Money',
            'SMALLMONEY' => 'Money',
            'POINT'      => 'GeoPoint',
            'POLYGON'    => 'GeoPolygon',
            'GEOMETRY'   => 'Geometry',
            'GEOGRAPHY'  => 'Geography',
            default      => null,
        };
    }

    /**
     * @param string $sqlType
     *
     * @return bool
     */
    public function shouldUseValueObject(string $sqlType) : bool
    {
        $baseType = $this->extractBaseType(sqlType: $sqlType);

        return in_array(needle: $baseType, haystack: [
            'UUID',
            'INET',
            'CIDR',
            'MACADDR',
            'MONEY',
            'SMALLMONEY',
            'POINT',
            'POLYGON',
            'GEOMETRY',
            'GEOGRAPHY',
        ],              strict: true);
    }

    public function getSupportedTypes() : array
    {
        return array_keys(array: self::TYPE_MAP);
    }

    public function isSupported(string $sqlType) : bool
    {
        $baseType = $this->extractBaseType(sqlType: $sqlType);

        return isset(self::TYPE_MAP[$baseType]);
    }
}
