<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Response\System\PublicSurface;

use Avax\Components\HTTP\Response\System\Capabilities\ResponseData\ResponseData;
use GuzzleHttp\Psr7\Utils;
use Psr\Http\Message\StreamInterface;

final class Response implements ResponseInterface
{
    private ResponseData $data;

    public function __construct(int $statusCode = 200, array $headers = [], StreamInterface $body = null, string $reasonPhrase = '', string $protocolVersion = '1.1')
    {
        if ($body === null) {
            $body = Utils::streamFor('');
        }
        $this->data = new ResponseData(
            statusCode     : $statusCode,
            headers        : $headers,
            body           : $body,
            reasonPhrase   : $reasonPhrase,
            protocolVersion: $protocolVersion,
        );
    }

    public function getProtocolVersion() : string
    {
        return $this->data->protocolVersion;
    }

    public function withProtocolVersion($v) : self
    {
        $c       = clone $this;
        $c->data = $this->data->withProtocolVersion($v);

        return $c;
    }

    public function getHeaders() : array
    {
        return $this->data->headers;
    }

    public function hasHeader($n) : bool
    {
        return isset($this->data->headers[strtolower($n)]);
    }

    public function getHeader($n) : array
    {
        return $this->data->headers[strtolower($n)] ?? [];
    }

    public function getHeaderLine($n) : string
    {
        return implode(', ', $this->getHeader($n));
    }

    public function withHeader($n, $v) : self
    {
        $c       = clone $this;
        $c->data = $this->data->withHeader($n, is_array($v) ? $v : [$v]);

        return $c;
    }

    public function withAddedHeader($n, $v) : self
    {
        return $this->withHeader($n, $v);
    }

    public function withoutHeader($n) : self
    {
        $c       = clone $this;
        $c->data = $this->data->withoutHeader($n);

        return $c;
    }

    public function getBody() : StreamInterface
    {
        return $this->data->body;
    }

    public function withBody(StreamInterface $b) : self
    {
        $c       = clone $this;
        $c->data = $this->data->withBody($b);

        return $c;
    }

    public function getStatusCode() : int
    {
        return $this->data->statusCode;
    }

    public function withStatus($c, $rp = '') : self
    {
        $cl       = clone $this;
        $cl->data = $this->data->withStatus($c, $rp);

        return $cl;
    }

    public function getReasonPhrase() : string
    {
        return $this->data->reasonPhrase;
    }
}
