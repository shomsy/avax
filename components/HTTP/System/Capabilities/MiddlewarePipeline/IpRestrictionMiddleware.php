<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\System\Capabilities\MiddlewarePipeline;

use Avax\Components\HTTP\Request\System\PublicSurface\RequestInterface;
use Avax\Components\HTTP\Response\System\PublicSurface\Response;
use Avax\Components\HTTP\Response\System\PublicSurface\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use RuntimeException;

/**
 * Base class for middleware that restricts access by IP address.
 */
abstract class IpRestrictionMiddleware implements MiddlewareInterface
{
    public function handle(RequestInterface $request, callable $next): ResponseInterface
    {
        $ip = $request->getServerParams()['REMOTE_ADDR'] ?? '0.0.0.0';

        if (! $this->isAllowedIp($ip)) {
            return $this->createForbiddenResponse();
        }

        return $next($request);
    }

    abstract protected function isAllowedIp(string $ipAddress): bool;

    protected function createForbiddenResponse(): ResponseInterface
    {
        // Return a minimal 403 response
        $responseClass = class_exists(Response::class)
            ? Response::class
            : null;

        if ($responseClass !== null) {
            return new $responseClass(403, ['Content-Type' => ['text/plain']], 'Forbidden');
        }

        // Fallback: anonymous class implementing ResponseInterface
        return new class () implements ResponseInterface {
            public function getStatusCode(): int
            {
                return 403;
            }

            public function withStatus(int $code, string $reasonPhrase = ''): self
            {
                return $this;
            }

            public function getReasonPhrase(): string
            {
                return 'Forbidden';
            }

            public function getProtocolVersion(): string
            {
                return '1.1';
            }

            public function withProtocolVersion(string $version): self
            {
                return $this;
            }

            public function getHeaders(): array
            {
                return ['Content-Type' => ['text/plain']];
            }

            public function hasHeader(string $name): bool
            {
                return isset($this->getHeaders()[$name]);
            }

            public function getHeader(string $name): array
            {
                return $this->getHeaders()[$name] ?? [];
            }

            public function getHeaderLine(string $name): string
            {
                return implode(', ', $this->getHeader($name));
            }

            public function withHeader(string $name, $value): self
            {
                return $this;
            }

            public function withAddedHeader(string $name, $value): self
            {
                return $this;
            }

            public function withoutHeader(string $name): self
            {
                return $this;
            }

            public function getBody() : StreamInterface
            {
                return new class ('Forbidden') implements StreamInterface {
                    private string $content;
                    private int    $position = 0;

                    public function __construct(string $content)
                    {
                        $this->content = $content;
                    }

                    public function __toString() : string
                    {
                        return $this->content;
                    }

                    public function close() : void
                    {
                        $this->content  = '';
                        $this->position = 0;
                    }

                    public function detach() : mixed
                    {
                        $this->close();

                        return null;
                    }

                    public function getSize() : int
                    {
                        return strlen($this->content);
                    }

                    public function tell() : int
                    {
                        return $this->position;
                    }

                    public function eof() : bool
                    {
                        return $this->position >= strlen($this->content);
                    }

                    public function isSeekable() : bool
                    {
                        return true;
                    }

                    public function seek(int $offset, int $whence = SEEK_SET) : void
                    {
                        $target = match ($whence) {
                            SEEK_SET => (int) $offset,
                            SEEK_CUR => $this->position + (int) $offset,
                            SEEK_END => strlen($this->content) + (int) $offset,
                            default  => throw new RuntimeException('Invalid stream seek mode.'),
                        };

                        if ($target < 0) {
                            throw new RuntimeException('Cannot seek before stream start.');
                        }

                        $this->position = $target;
                    }

                    public function rewind() : void
                    {
                        $this->position = 0;
                    }

                    public function isWritable() : bool
                    {
                        return true;
                    }

                    public function write(string $string) : int
                    {
                        $string = (string) $string;
                        $before = substr($this->content, 0, $this->position);
                        $after  = substr($this->content, $this->position + strlen($string));

                        $this->content  = $before . $string . $after;
                        $this->position += strlen($string);

                        return strlen($string);
                    }

                    public function isReadable() : bool
                    {
                        return true;
                    }

                    public function read(int $length) : string
                    {
                        $chunk          = substr($this->content, $this->position, (int) $length);
                        $this->position += strlen($chunk);

                        return $chunk;
                    }

                    public function getContents() : string
                    {
                        $contents       = substr($this->content, $this->position);
                        $this->position = strlen($this->content);

                        return $contents;
                    }

                    public function getMetadata(string|null $key = null) : mixed
                    {
                        if ($key === null) {
                            return [];
                        }

                        return null;
                    }
                };
            }

            public function withBody(mixed $body): self
            {
                return $this;
            }
        };
    }
}
