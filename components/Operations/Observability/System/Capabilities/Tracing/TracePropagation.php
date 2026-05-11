<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Observability\System\Capabilities\Tracing;

use Avax\Components\Operations\Observability\System\Capabilities\Correlation\SpanId;
use Avax\Components\Operations\Observability\System\Capabilities\Correlation\TraceId;
use InvalidArgumentException;
use function sprintf;

/**
 * Trace propagation context for distributed tracing.
 *
 * Carries trace context across process and service boundaries.
 * Compatible with W3C TraceContext header format for interoperability.
 *
 * W3C TraceContext header: traceparent=00-{traceId}-{spanId}-{flags}
 */
final readonly class TracePropagation
{
    public function __construct(
        public TraceId $traceId,
        public SpanId $spanId,
        public SpanId|null $parentSpanId = null,
        public bool $sampled = true,
    ) {}

    /**
     * Create a new trace propagation context.
     */
    public static function start() : self
    {
        return new self(
            traceId: TraceId::generate(),
            spanId: SpanId::generate(),
        );
    }

    /**
     * Create a child span propagation context.
     */
    public function child() : self
    {
        return new self(
            traceId: $this->traceId,
            spanId: SpanId::generate(),
            parentSpanId: $this->spanId,
            sampled: $this->sampled,
        );
    }

    /**
     * Parse W3C TraceContext traceparent header.
     *
     * Format: 00-{traceId}-{spanId}-{flags}
     * Example: 00-4bf92f3577b34da6a3ce929d0e0e4736-00f067aa0ba902b7-01
     */
    public static function fromTraceparent(string $header) : self
    {
        $parts = explode('-', $header);

        if (count($parts) !== 4 || $parts[0] !== '00') {
            throw new InvalidArgumentException("Invalid traceparent header: {$header}");
        }

        $flags = hexdec($parts[3]);

        return new self(
            traceId: TraceId::fromString($parts[1]),
            spanId: SpanId::fromString($parts[2]),
            sampled: ($flags & 1) === 1,
        );
    }

    /**
     * Serialize to W3C TraceContext traceparent header.
     */
    public function toTraceparent() : string
    {
        $flags = $this->sampled ? '01' : '00';

        return sprintf(
            '00-%s-%s-%s',
            $this->traceId,
            $this->spanId,
            $flags,
        );
    }

    /**
     * Convert to an array suitable for HTTP headers.
     *
     * @return array{traceparent: string, tracestate?: string}
     */
    public function toHeaders() : array
    {
        return ['traceparent' => $this->toTraceparent()];
    }
}
