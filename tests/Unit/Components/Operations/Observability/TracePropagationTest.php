<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Operations\Observability;

use Avax\Components\Operations\Observability\System\Capabilities\Tracing\TracePropagation;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class TracePropagationTest extends TestCase
{
    #[Test]
    public function start_creates_new_trace_context() : void
    {
        $ctx = TracePropagation::start();

        self::assertNotEmpty($ctx->traceId->value);
        self::assertNotEmpty($ctx->spanId->value);
        self::assertNull($ctx->parentSpanId);
        self::assertTrue($ctx->sampled);
    }

    #[Test]
    public function child_creates_child_span_with_same_trace_id() : void
    {
        $parent = TracePropagation::start();
        $child = $parent->child();

        self::assertSame($parent->traceId->value, $child->traceId->value);
        self::assertNotSame($parent->spanId->value, $child->spanId->value);
        self::assertNotNull($child->parentSpanId);
        self::assertSame($parent->spanId->value, $child->parentSpanId->value);
    }

    #[Test]
    public function toTraceparent_produces_valid_header() : void
    {
        $ctx = new TracePropagation(
            traceId: \Avax\Components\Operations\Observability\System\Capabilities\Correlation\TraceId::fromString('4bf92f3577b34da6a3ce929d0e0e4736'),
            spanId: \Avax\Components\Operations\Observability\System\Capabilities\Correlation\SpanId::fromString('00f067aa0ba902b7'),
            sampled: true,
        );

        $header = $ctx->toTraceparent();

        self::assertSame('00-4bf92f3577b34da6a3ce929d0e0e4736-00f067aa0ba902b7-01', $header);
    }

    #[Test]
    public function fromTraceparent_parses_valid_header() : void
    {
        $header = '00-4bf92f3577b34da6a3ce929d0e0e4736-00f067aa0ba902b7-01';
        $ctx = TracePropagation::fromTraceparent($header);

        self::assertSame('4bf92f3577b34da6a3ce929d0e0e4736', $ctx->traceId->value);
        self::assertSame('00f067aa0ba902b7', $ctx->spanId->value);
        self::assertTrue($ctx->sampled);
    }

    #[Test]
    public function fromTraceparent_parses_not_sampled() : void
    {
        $header = '00-4bf92f3577b34da6a3ce929d0e0e4736-00f067aa0ba902b7-00';
        $ctx = TracePropagation::fromTraceparent($header);

        self::assertFalse($ctx->sampled);
    }

    #[Test]
    public function fromTraceparent_rejects_invalid_header() : void
    {
        self::expectException(InvalidArgumentException::class);

        TracePropagation::fromTraceparent('invalid');
    }

    #[Test]
    public function toHeaders_returns_header_array() : void
    {
        $ctx = TracePropagation::start();
        $headers = $ctx->toHeaders();

        self::assertArrayHasKey('traceparent', $headers);
        self::assertStringStartsWith('00-', $headers['traceparent']);
    }

    #[Test]
    public function roundtrip_serialization() : void
    {
        $original = TracePropagation::start();
        $header = $original->toTraceparent();
        $parsed = TracePropagation::fromTraceparent($header);

        self::assertSame($original->traceId->value, $parsed->traceId->value);
        self::assertSame($original->spanId->value, $parsed->spanId->value);
        self::assertSame($original->sampled, $parsed->sampled);
    }
}
