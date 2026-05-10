<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Response\System\Capabilities;

/**
 * Canonical HTTP content types as a backed enum.
 *
 * Replaces bare string literals like 'application/json', 'text/html', etc.
 * with type-safe enum cases.
 */
enum ContentType: string
{
    case Json = 'application/json';
    case Html = 'text/html';
    case Plain = 'text/plain';
    case Xml = 'application/xml';
    case FormUrlEncoded = 'application/x-www-form-urlencoded';
    case Multipart = 'multipart/form-data';
    case OctetStream = 'application/octet-stream';
    case Css = 'text/css';
    case JavaScript = 'application/javascript';
    case Csv = 'text/csv';

    /**
     * Create a ContentType from a string value.
     */
    public static function fromString(string $type): self
    {
        $lower = strtolower($type);

        return match ($lower) {
            'application/json' => self::Json,
            'text/html', 'text/html; charset=utf-8' => self::Html,
            'text/plain', 'text/plain; charset=utf-8' => self::Plain,
            'application/xml', 'text/xml' => self::Xml,
            'application/x-www-form-urlencoded' => self::FormUrlEncoded,
            'multipart/form-data' => self::Multipart,
            'application/octet-stream' => self::OctetStream,
            'text/css' => self::Css,
            'application/javascript', 'text/javascript' => self::JavaScript,
            'text/csv' => self::Csv,
            default => self::Plain,
        };
    }

    /**
     * Get the content type with charset appended.
     */
    public function withCharset(string $charset = 'utf-8'): string
    {
        return match ($this) {
            self::Html, self::Plain => "{$this->value}; charset={$charset}",
            default => $this->value,
        };
    }
}
