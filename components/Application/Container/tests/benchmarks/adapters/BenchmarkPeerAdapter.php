<?php

declare(strict_types=1);

/**
 * Loads one governed benchmark artifact for peer comparison.
 */
interface BenchmarkPeerAdapter
{
    public function name(): string;

    /**
     * @return array{meta: array<string, mixed>, results: array<string, array<string, mixed>>}
     */
    public function load(): array;
}
