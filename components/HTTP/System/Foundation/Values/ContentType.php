<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\System\Foundation\Values;

use ValueError;
use function str_starts_with;
use function strtolower;
use function trim;

enum ContentType: string
{
    case APPLICATION_JSON            = 'application/json';
    case APPLICATION_XML             = 'application/xml';
    case TEXT_HTML                   = 'text/html';
    case TEXT_PLAIN                  = 'text/plain';
    case TEXT_XML                    = 'text/xml';
    case TEXT_CSS                    = 'text/css';
    case TEXT_CSV                    = 'text/csv';
    case APPLICATION_JAVASCRIPT      = 'application/javascript';
    case APPLICATION_FORM_URLENCODED = 'application/x-www-form-urlencoded';
    case MULTIPART_FORM_DATA         = 'multipart/form-data';
    case APPLICATION_OCTET_STREAM    = 'application/octet-stream';
    case APPLICATION_PDF             = 'application/pdf';
    case APPLICATION_ZIP             = 'application/zip';
    case APPLICATION_GZIP            = 'application/gzip';
    case IMAGE_PNG                   = 'image/png';
    case IMAGE_JPEG                  = 'image/jpeg';
    case IMAGE_GIF                   = 'image/gif';
    case IMAGE_SVG_XML               = 'image/svg+xml';
    case IMAGE_WEBP                  = 'image/webp';
    case IMAGE_ICON                  = 'image/x-icon';
    case APPLICATION_ATOM_XML        = 'application/atom+xml';
    case APPLICATION_RSS_XML         = 'application/rss+xml';
    case APPLICATION_GRAPHQL         = 'application/graphql+json';
    case APPLICATION_PROBLEM_JSON    = 'application/problem+json';
    case APPLICATION_PROBLEM_XML     = 'application/problem+xml';
    case APPLICATION_YAML            = 'application/yaml';
    case APPLICATION_TOML            = 'application/toml';
    case TEXT_MARKDOWN               = 'text/markdown';
    case TEXT_CALENDAR               = 'text/calendar';
    case EVENT_STREAM                = 'text/event-stream';

    /**
     * Default charset used when a charset parameter is requested.
     */
    private const DEFAULT_CHARSET = 'utf-8';

    /**
     * Create a ContentType from a MIME type string.
     *
     * @throws ValueError if the MIME type is not recognized
     */
    public static function fromMimeType(string $mimeType) : self
    {
        $normalized = strtolower(trim($mimeType));

        return self::from($normalized);
    }

    /**
     * Check if a string is a valid, recognized content type.
     */
    public static function isValid(string $mimeType) : bool
    {
        return self::tryFromMimeType($mimeType) !== null;
    }

    /**
     * Try to create a ContentType from a MIME type string.
     * Returns null if the MIME type is not recognized.
     */
    public static function tryFromMimeType(string $mimeType) : ?self
    {
        $normalized = strtolower(trim($mimeType));

        return self::tryFrom($normalized);
    }

    /**
     * Get the full MIME type string.
     * This is equivalent to accessing the backed value directly.
     */
    public function mimeType() : string
    {
        return $this->value;
    }

    /**
     * Get the MIME type with charset parameter appended.
     *
     * @param string|null $charset Override the default charset. If null, uses utf-8.
     */
    public function mimeTypeWithCharset(?string $charset = null) : string
    {
        $charset ??= self::DEFAULT_CHARSET;

        return match ($this) {
            // Content types that support charset parameter
            self::APPLICATION_JSON,
            self::APPLICATION_XML,
            self::TEXT_HTML,
            self::TEXT_PLAIN,
            self::TEXT_XML,
            self::TEXT_CSS,
            self::TEXT_CSV,
            self::APPLICATION_JAVASCRIPT,
            self::APPLICATION_FORM_URLENCODED,
            self::APPLICATION_PROBLEM_JSON,
            self::APPLICATION_PROBLEM_XML,
            self::APPLICATION_YAML,
            self::APPLICATION_TOML,
            self::TEXT_MARKDOWN,
            self::TEXT_CALENDAR => "{$this->value}; charset={$charset}",

            // Content types that do NOT support charset
            default             => $this->value,
        };
    }

    /**
     * Check if this content type is a text-based type (supports charset).
     */
    public function isTextBased() : bool
    {
        return $this->charset() !== null;
    }

    /**
     * Get the default charset for this content type, if applicable.
     * Returns null for binary or charset-irrelevant content types.
     */
    public function charset() : ?string
    {
        return match ($this) {
            self::APPLICATION_JSON,
            self::APPLICATION_XML,
            self::TEXT_HTML,
            self::TEXT_PLAIN,
            self::TEXT_XML,
            self::TEXT_CSS,
            self::TEXT_CSV,
            self::APPLICATION_JAVASCRIPT,
            self::APPLICATION_FORM_URLENCODED,
            self::APPLICATION_PROBLEM_JSON,
            self::APPLICATION_PROBLEM_XML,
            self::APPLICATION_YAML,
            self::APPLICATION_TOML,
            self::TEXT_MARKDOWN,
            self::TEXT_CALENDAR,
            self::EVENT_STREAM => self::DEFAULT_CHARSET,

            default            => null,
        };
    }

    /**
     * Check if this content type is JSON.
     */
    public function isJson() : bool
    {
        return match ($this) {
            self::APPLICATION_JSON,
            self::APPLICATION_GRAPHQL,
            self::APPLICATION_PROBLEM_JSON => true,
            default                        => false,
        };
    }

    /**
     * Check if this content type is XML.
     */
    public function isXml() : bool
    {
        return match ($this) {
            self::APPLICATION_XML,
            self::TEXT_XML,
            self::APPLICATION_ATOM_XML,
            self::APPLICATION_RSS_XML,
            self::APPLICATION_PROBLEM_XML => true,
            default                       => false,
        };
    }

    /**
     * Check if this content type is an image type.
     */
    public function isImage() : bool
    {
        return str_starts_with($this->value, 'image/');
    }

    /**
     * Check if this content type is a form type.
     */
    public function isForm() : bool
    {
        return match ($this) {
            self::APPLICATION_FORM_URLENCODED,
            self::MULTIPART_FORM_DATA => true,
            default                   => false,
        };
    }

    /**
     * Check if this content type represents an error/problem response.
     */
    public function isProblem() : bool
    {
        return match ($this) {
            self::APPLICATION_PROBLEM_JSON,
            self::APPLICATION_PROBLEM_XML => true,
            default                       => false,
        };
    }
}
