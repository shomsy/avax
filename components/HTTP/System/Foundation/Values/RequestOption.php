<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\System\Foundation\Values;

use ValueError;

use function in_array;
use function is_bool;
use function is_callable;
use function is_float;
use function is_int;
use function is_string;
use function strtolower;
use function trim;

enum RequestOption: string
{
    case TIMEOUT = 'timeout';
    case CONNECT_TIMEOUT = 'connect_timeout';
    case VERIFY_SSL = 'verify_ssl';
    case PROXY = 'proxy';
    case HEADERS_ONLY = 'headers_only';
    case FOLLOW_REDIRECTS = 'follow_redirects';
    case MAX_REDIRECTS = 'max_redirects';
    case RETRY_COUNT = 'retry_count';
    case RETRY_DELAY = 'retry_delay';
    case SSL_CERT = 'ssl_cert';
    case SSL_KEY = 'ssl_key';
    case SSL_CA_PATH = 'ssl_ca_path';
    case SSL_CA_FILE = 'ssl_ca_file';
    case HTTP_VERSION = 'http_version';
    case DECODE_CONTENT = 'decode_content';
    case STREAM_RESPONSE = 'stream_response';
    case ON_HEADERS = 'on_headers';
    case ON_PROGRESS = 'on_progress';
    case ON_STATS = 'on_stats';
    case SYNCHRONOUS = 'synchronous';

    /**
     * Create a RequestOption from an option name string.
     *
     * @throws ValueError if the option name is not recognized
     */
    public static function fromName(string $name): self
    {
        return self::from(strtolower(trim($name)));
    }

    /**
     * Check if a string is a valid, recognized request option name.
     */
    public static function isValid(string $name): bool
    {
        return self::tryFromName($name) instanceof RequestOption;
    }

    /**
     * Try to create a RequestOption from an option name string.
     * Returns null if the option name is not recognized.
     */
    public static function tryFromName(string $name): ?self
    {
        return self::tryFrom(strtolower(trim($name)));
    }

    /**
     * Get the default value for this option.
     */
    public function defaultValue(): mixed
    {
        return match ($this) {
            self::TIMEOUT => 30.0,
            self::CONNECT_TIMEOUT => 10.0,
            self::VERIFY_SSL => true,
            self::PROXY => null,
            self::HEADERS_ONLY => false,
            self::FOLLOW_REDIRECTS => true,
            self::MAX_REDIRECTS => 5,
            self::RETRY_COUNT => 0,
            self::RETRY_DELAY => 1.0,
            self::SSL_CERT => null,
            self::SSL_KEY => null,
            self::SSL_CA_PATH => null,
            self::SSL_CA_FILE => null,
            self::HTTP_VERSION => 0, // Use default/auto-negotiate
            self::DECODE_CONTENT => true,
            self::STREAM_RESPONSE => false,
            self::ON_HEADERS => null,
            self::ON_PROGRESS => null,
            self::ON_STATS => null,
            self::SYNCHRONOUS => true,
        };
    }

    /**
     * Check if this option is a boolean flag.
     */
    public function isBoolean(): bool
    {
        return $this->defaultValueType() === 'bool';
    }

    /**
     * Get the default value type for this option.
     *
     * @return 'int'|'float'|'bool'|'string'|'array'|'callable'|'null'
     */
    public function defaultValueType(): string
    {
        return match ($this) {
            self::TIMEOUT,
            self::CONNECT_TIMEOUT => 'float',

            self::VERIFY_SSL,
            self::HEADERS_ONLY,
            self::FOLLOW_REDIRECTS,
            self::DECODE_CONTENT,
            self::SYNCHRONOUS => 'bool',

            self::MAX_REDIRECTS,
            self::RETRY_COUNT,
            self::HTTP_VERSION => 'int',

            self::PROXY,
            self::SSL_CERT,
            self::SSL_KEY,
            self::SSL_CA_PATH,
            self::SSL_CA_FILE => 'string',

            self::RETRY_DELAY => 'float',

            self::ON_HEADERS,
            self::ON_PROGRESS,
            self::ON_STATS => 'callable',

            self::STREAM_RESPONSE => 'bool',
        };
    }

    /**
     * Check if this option is a numeric value (int or float).
     */
    public function isNumeric(): bool
    {
        return in_array($this->defaultValueType(), ['int', 'float'], true);
    }

    /**
     * Check if this option is a callable/callback.
     */
    public function isCallable(): bool
    {
        return $this->defaultValueType() === 'callable';
    }

    /**
     * Check if this option is SSL/TLS related.
     */
    public function isSslRelated(): bool
    {
        return match ($this) {
            self::VERIFY_SSL,
            self::SSL_CERT,
            self::SSL_KEY,
            self::SSL_CA_PATH,
            self::SSL_CA_FILE => true,
            default => false,
        };
    }

    /**
     * Check if this option is a timeout setting.
     */
    public function isTimeout(): bool
    {
        return match ($this) {
            self::TIMEOUT,
            self::CONNECT_TIMEOUT => true,
            default => false,
        };
    }

    /**
     * Check if this option is a retry setting.
     */
    public function isRetryRelated(): bool
    {
        return match ($this) {
            self::RETRY_COUNT,
            self::RETRY_DELAY => true,
            default => false,
        };
    }

    /**
     * Check if this option is an event callback.
     */
    public function isEventCallback(): bool
    {
        return match ($this) {
            self::ON_HEADERS,
            self::ON_PROGRESS,
            self::ON_STATS => true,
            default => false,
        };
    }

    /**
     * Validate that a value is appropriate for this option type.
     *
     * @param  mixed  $value  The value to validate
     * @return bool Whether the value is valid for this option
     */
    public function isValidValue(mixed $value): bool
    {
        if ($value === null) {
            return true; // Allow null for nullable options
        }

        return match ($this) {
            self::TIMEOUT,
            self::CONNECT_TIMEOUT => is_float($value) || is_int($value),

            self::VERIFY_SSL,
            self::HEADERS_ONLY,
            self::FOLLOW_REDIRECTS,
            self::DECODE_CONTENT,
            self::SYNCHRONOUS => is_bool($value),

            self::MAX_REDIRECTS,
            self::RETRY_COUNT => is_int($value),

            self::HTTP_VERSION => is_int($value),

            self::PROXY,
            self::SSL_CERT,
            self::SSL_KEY,
            self::SSL_CA_PATH,
            self::SSL_CA_FILE => is_string($value),

            self::RETRY_DELAY => is_float($value) || is_int($value),

            self::ON_HEADERS,
            self::ON_PROGRESS,
            self::ON_STATS => is_callable($value),

            self::STREAM_RESPONSE => is_bool($value),
        };
    }
}
