<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Response\Capabilities\Cookies;

use DateTimeImmutable;
use DateTimeInterface;
use InvalidArgumentException;

final readonly class ResponseCookie
{
    public function __construct(
        public string                 $name,
        public string                 $value = '',
        public DateTimeInterface|null $expiresAt = null,
        public string                 $path = '/',
        public string|null            $domain = null,
        public bool                   $secure = false,
        public bool                   $httpOnly = true,
        public string|null            $sameSite = 'Lax',
    )
    {
        if ($this->name === '') {
            throw new InvalidArgumentException(message: 'Cookie name cannot be empty.');
        }
    }

    public static function expired(string $name, string|null $path = null, string|null $domain = null) : self
    {
        $path ??= '/';

        return new self(
            name     : $name,
            value    : '',
            expiresAt: new DateTimeImmutable(datetime: '@1'),
            path     : $path,
            domain   : $domain,
        );
    }

    public function toHeaderValue() : string
    {
        $parts = [rawurlencode(string: $this->name) . '=' . rawurlencode(string: $this->value)];

        if ($this->expiresAt !== null) {
            $parts[] = 'Expires=' . gmdate(format: 'D, d M Y H:i:s', timestamp: $this->expiresAt->getTimestamp()) . ' GMT';
        }

        if ($this->path !== '') {
            $parts[] = 'Path=' . $this->path;
        }

        if ($this->domain !== null && $this->domain !== '') {
            $parts[] = 'Domain=' . $this->domain;
        }

        if ($this->secure) {
            $parts[] = 'Secure';
        }

        if ($this->httpOnly) {
            $parts[] = 'HttpOnly';
        }

        if ($this->sameSite !== null && $this->sameSite !== '') {
            $parts[] = 'SameSite=' . $this->sameSite;
        }

        return implode(separator: '; ', array: $parts);
    }
}
