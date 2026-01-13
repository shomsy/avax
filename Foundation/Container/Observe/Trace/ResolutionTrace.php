<?php

declare(strict_types=1);

namespace Avax\Container\Observe\Trace;

use Avax\Container\Core\Kernel\ResolutionState;

/**
 * Immutable trace of a single resolution pipeline execution.
 *
 * @see docs/Observe/Trace/ResolutionTrace.md#quick-summary
 */
final readonly class ResolutionTrace
{
    /**
     * @param list<array{stage: string, outcome: string, state: ResolutionState}> $entries
     */
    public function __construct(
        public array $entries = []
    ) {}

    /**
     * Rehydrate a trace from array form.
     *
     * @param list<array{stage: string, outcome: string, state: string}> $entries
     */
    public static function fromArray(array $entries) : self
    {
        $reconstructed = array_map(
            static fn(array $entry) : array => [
                'stage'   => $entry['stage'],
                'outcome' => $entry['outcome'],
                'state'   => ResolutionState::from(value: $entry['state']),
            ],
            $entries
        );

        return new self(entries: $reconstructed);
    }

    /**
     * Record a new stage outcome.
     */
    public function record(ResolutionState $state, string $stage, string $outcome) : self
    {
        $entries   = $this->entries;
        $entries[] = [
            'stage'   => $stage,
            'outcome' => $outcome,
            'state'   => $state,
        ];

        return new self(entries: $entries);
    }

    /**
     * Export trace as an array suitable for JSON serialization.
     *
     * @return list<array{stage: string, outcome: string, state: string}>
     */
    public function toArray() : array
    {
        return array_map(
            static fn(array $entry) : array => [
                'stage'   => $entry['stage'],
                'outcome' => $entry['outcome'],
                'state'   => $entry['state']->value,
            ],
            $this->entries
        );
    }
}
