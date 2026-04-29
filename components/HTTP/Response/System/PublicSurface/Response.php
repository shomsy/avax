<?php
declare(strict_types=1);

namespace Avax\Components\HTTP\Response\System\PublicSurface;

use Psr\Http\Message\StreamInterface;

final class Response implements ResponseInterface {
    public function __construct(private int $sc = 200, private array $h = [], private ?StreamInterface $b = null, private string $rp = '', private string $pv = '1.1') {
        if ($this->b === null) $this->b = \GuzzleHttp\Psr7\Utils::streamFor('');
    }
    public function getProtocolVersion(): string { return $this->pv; }
    public function withProtocolVersion($v): self { $c = clone $this; $c->pv = $v; return $c; }
    public function getHeaders(): array { return $this->h; }
    public function hasHeader($n): bool { return isset($this->h[strtolower($n)]); }
    public function getHeader($n): array { return $this->h[strtolower($n)] ?? []; }
    public function getHeaderLine($n): string { return implode(', ', $this->getHeader($n)); }
    public function withHeader($n, $v): self { $c = clone $this; $c->h[strtolower($n)] = is_array($v) ? $v : [$v]; return $c; }
    public function withAddedHeader($n, $v): self { return $this->withHeader($n, $v); }
    public function withoutHeader($n): self { $c = clone $this; unset($c->h[strtolower($n)]); return $c; }
    public function getBody(): StreamInterface { return $this->b; }
    public function withBody(StreamInterface $b): self { $c = clone $this; $c->b = $b; return $c; }
    public function getStatusCode(): int { return $this->sc; }
    public function withStatus($c, $rp = ''): self { $cl = clone $this; $cl->sc = $c; $cl->rp = $rp; return $cl; }
    public function getReasonPhrase(): string { return $this->rp; }
}
