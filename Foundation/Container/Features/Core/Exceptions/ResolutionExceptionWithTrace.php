<?php

declare(strict_types=1);

namespace Avax\Container\Features\Core\Exceptions;

use Avax\Container\Observe\Trace\ResolutionTrace;
use JsonSerializable;
use Psr\Container\NotFoundExceptionInterface;
use Throwable;

/**
 * Resolution exception that carries the execution trace.
 *
 * @see docs/Features/Core/Exceptions/ResolutionExceptionWithTrace.md#quick-summary
 */
class ResolutionExceptionWithTrace extends ResolutionException implements JsonSerializable, NotFoundExceptionInterface
{
    public function __construct(
        private readonly ResolutionTrace $trace,
        string                           $message = '',
        int                              $code = 0,
        Throwable|null                   $previous = null
    )
    {
        parent::__construct(message: $message, code: $code, previous: $previous);
    }

    /**
     * Access the recorded resolution trace.
     *
     * @see docs/Features/Core/Exceptions/ResolutionExceptionWithTrace.md#method-trace
     */
    public function trace() : ResolutionTrace
    {
        return $this->trace;
    }

    /**
     * Short, human-readable representation of the exception and trace.
     *
     * @see docs/Features/Core/Exceptions/ResolutionExceptionWithTrace.md#method-__tostring
     */
    public function __toString() : string
    {
        $entries = array_slice($this->trace->toArray(), 0, 10);
        $lines   = array_map(
            static fn(array $entry) : string => sprintf('[%s] %s => %s', $entry['state'], $entry['stage'], $entry['outcome']),
            $entries
        );

        $summary = implode(PHP_EOL, $lines);

        return trim($this->getMessage() . PHP_EOL . $summary);
    }

    /**
     * Serialize the exception payload for JSON transport.
     *
     * @see docs/Features/Core/Exceptions/ResolutionExceptionWithTrace.md#method-jsonserialize
     */
    public function jsonSerialize() : array
    {
        return [
            'message' => $this->getMessage(),
            'trace'   => $this->trace->toArray(),
        ];
    }
}
