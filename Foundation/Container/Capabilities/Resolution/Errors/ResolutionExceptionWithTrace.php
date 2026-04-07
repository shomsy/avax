<?php

declare(strict_types=1);

namespace Avax\Container\Capabilities\Resolution\Errors;

use Avax\Container\Capabilities\Observability\Trace\ResolutionTrace;
use JsonSerializable;
use Psr\Container\NotFoundExceptionInterface;
use Throwable;

/**
 * Resolution exception that carries the execution trace.
 *
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
     */
    public function trace() : ResolutionTrace
    {
        return $this->trace;
    }

    /**
     * Short, human-readable representation of the exception and trace.
     *
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
     */
    public function jsonSerialize() : array
    {
        return [
            'message' => $this->getMessage(),
            'trace'   => $this->trace->toArray(),
        ];
    }
}
