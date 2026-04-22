<?php

declare(strict_types=1);

namespace Avax\DataHandling\Traits\Search;

use FuzzyWuzzy\Fuzz;
use InvalidArgumentException;

/**
 * Provides fuzzy search operations: fuzzyMatch, levenshteinSearch, partialMatch, phoneticMatch.
 *
 * Combines functionality that was previously duplicated across multiple traits.
 */
trait FuzzySearchTrait
{
    abstract protected function getItems(): array;

    private ?Fuzz $fuzz = null;

    private function getFuzz(): Fuzz
    {
        return $this->fuzz ??= new Fuzz();
    }

    public function fuzzyMatch(
        string $query,
        int $threshold = 70,
        ?string $key = null,
    ): static {
        $this->validateThreshold($threshold);

        $filtered = array_filter(
            $this->getItems(),
            function (mixed $item) use ($query, $threshold, $key): bool {
                $target = $key !== null ? ($item[$key] ?? '') : $item;

                if (! is_string($target)) {
                    return false;
                }

                return $this->getFuzz()->ratio(strtolower($query), strtolower($target)) >= $threshold;
            }
        );

        return $this->withItems(array_values($filtered));
    }

    public function levenshteinSearch(
        string $query,
        int $maxDistance = 2,
        ?string $key = null,
    ): static {
        if ($maxDistance < 0) {
            throw new InvalidArgumentException('Maximum distance cannot be negative.');
        }

        $matched = [];

        foreach ($this->getItems() as $item) {
            $target = $key !== null ? ($item[$key] ?? '') : $item;

            if (! is_string($target)) {
                continue;
            }

            $distance = levenshtein(strtolower($query), strtolower($target));

            if ($distance <= $maxDistance) {
                $matched[$distance][] = $item;
            }
        }

        ksort($matched);

        $sorted = [];

        foreach ($matched as $items) {
            foreach ($items as $item) {
                $sorted[] = $item;
            }
        }

        return $this->withItems($sorted);
    }

    public function partialMatch(string $query, ?string $key = null): static
    {
        $filtered = array_filter(
            $this->getItems(),
            function (mixed $item) use ($query, $key): bool {
                $target = $key !== null ? ($item[$key] ?? '') : $item;

                if (! is_string($target)) {
                    return false;
                }

                return stripos($target, $query) !== false;
            }
        );

        return $this->withItems(array_values($filtered));
    }

    public function phoneticMatch(string $query, ?string $key = null): static
    {
        $queryPhonetic = metaphone(strtolower($query));

        $filtered = array_filter(
            $this->getItems(),
            function (mixed $item) use ($queryPhonetic, $key): bool {
                $target = $key !== null ? ($item[$key] ?? '') : $item;

                if (! is_string($target)) {
                    return false;
                }

                return metaphone(strtolower($target)) === $queryPhonetic;
            }
        );

        return $this->withItems(array_values($filtered));
    }

    public function regexSearch(string $pattern, ?string $key = null): static
    {
        if (@preg_match($pattern, '') === false) {
            throw new InvalidArgumentException('Invalid regular expression pattern.');
        }

        $filtered = array_filter(
            $this->getItems(),
            function (mixed $item) use ($pattern, $key): bool {
                $target = $key !== null ? ($item[$key] ?? '') : $item;

                if (! is_string($target)) {
                    return false;
                }

                return preg_match($pattern, $target) === 1;
            }
        );

        return $this->withItems(array_values($filtered));
    }

    public function tokenSortMatch(
        string $query,
        int $threshold = 70,
        ?string $key = null,
    ): static {
        $this->validateThreshold($threshold);

        $sortedQuery = $this->sortTokens($query);

        $filtered = array_filter(
            $this->getItems(),
            function (mixed $item) use ($sortedQuery, $threshold, $key): bool {
                $target = $key !== null ? ($item[$key] ?? '') : $item;

                if (! is_string($target)) {
                    return false;
                }

                $sortedTarget = $this->sortTokens($target);

                return $this->getFuzz()->ratio($sortedQuery, $sortedTarget) >= $threshold;
            }
        );

        return $this->withItems(array_values($filtered));
    }

    public function tokenSetMatch(
        string $query,
        int $threshold = 70,
        ?string $key = null,
    ): static {
        $this->validateThreshold($threshold);

        $sortedQuery = implode(' ', $this->sortTokens($query));

        $filtered = array_filter(
            $this->getItems(),
            function (mixed $item) use ($sortedQuery, $threshold, $key): bool {
                $target = $key !== null ? ($item[$key] ?? '') : $item;

                if (! is_string($target)) {
                    return false;
                }

                $sortedTarget = implode(' ', $this->sortTokens($target));

                return $this->getFuzz()->ratio($sortedQuery, $sortedTarget) >= $threshold;
            }
        );

        return $this->withItems(array_values($filtered));
    }

    public function sortBySimilarity(string $query, ?string $key = null): array
    {
        $queryLower = strtolower($query);

        $items = $this->getItems();

        usort(
            $items,
            function (mixed $a, mixed $b) use ($key, $queryLower): int {
                $aValue = $key !== null ? ($a[$key] ?? '') : $a;
                $bValue = $key !== null ? ($b[$key] ?? '') : $b;

                if (! is_string($aValue) || ! is_string($bValue)) {
                    return 0;
                }

                $similarityA = $this->getFuzz()->ratio($queryLower, strtolower($aValue));
                $similarityB = $this->getFuzz()->ratio($queryLower, strtolower($bValue));

                return $similarityB <=> $similarityA;
            }
        );

        return $items;
    }

    private function sortTokens(string $string): array
    {
        $tokens = explode(' ', strtolower($string));
        sort($tokens);

        return $tokens;
    }

    private function validateThreshold(int $threshold): void
    {
        if ($threshold < 0 || $threshold > 100) {
            throw new InvalidArgumentException('Threshold must be between 0 and 100.');
        }
    }

    abstract protected function withItems(array $items): static;
}