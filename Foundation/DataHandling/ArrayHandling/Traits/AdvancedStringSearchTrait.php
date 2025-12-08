<?php

declare(strict_types=1);

namespace Avax\DataHandling\ArrayHandling\Traits;

use FuzzyWuzzy\Fuzz;
use FuzzyWuzzy\Process;
use InvalidArgumentException;

/**
 * Trait AdvancedStringSearchTrait
 *
 * Provides advanced string search functionalities for collections, including fuzzy search and similarity-based search.
 *
 * @package Avax\DataHandling\ArrayHandling\Traits
 */
trait AdvancedStringSearchTrait
{
    /**
     * Performs a fuzzy match on the collection items based on a given query.
     *
     * This method calculates the similarity ratio between the query and each item using FuzzyWuzzy,
     * returning items that meet or exceed the specified similarity threshold.
     *
     * @param string      $query     The search query string.
     * @param float|null  $threshold The minimum similarity percentage (0 to 100) required for a match.
     * @param string|null $key       The key to search within if items are associative arrays.
     *
     * @return static A new instance containing the matched items.
     *
     * @example
     * $arrh = new Arrhae(['apple', 'banana', 'apricot', 'grape']);
     * $results = $arrh->fuzzyMatch('aple', 80);
     * // Returns ['apple']
     *
     * $arrhAssoc = new Arrhae([
     *     ['name' => 'Alice'],
     *     ['name' => 'Alicia'],
     *     ['name' => 'Alina'],
     *     ['name' => 'Bob'],
     * ]);
     * $resultsAssoc = $arrhAssoc->fuzzyMatch('Alic', 70, 'name');
     * // Returns [
     * //     ['name' => 'Alice'],
     * //     ['name' => 'Alicia'],
     * //     ['name' => 'Alina'],
     * // ]
     */
    public function fuzzyMatch(string $query, float|null $threshold = null, string|null $key = null) : static
    {
        $threshold ??= 70.0;
        $this->isProperThreshold($threshold);

        $fuzz    = new Fuzz();
        $process = new Process(fuzz: $fuzz);

        $matchedItems = array_filter(
            $this->getItems(),
            function ($item) use ($key, $fuzz, $query, $threshold) : bool {
                $target = $key !== null ? ($item[$key] ?? '') : $item;

                if (! is_string($target)) {
                    return false;
                }

                $similarity = $fuzz->ratio(s1: strtolower($query), s2: strtolower($target));

                return $similarity >= $threshold;
            }
        );

        return new static(items: array_values($matchedItems));
    }

    /**
     * Helper method to validate the threshold.
     *
     * @param float $threshold The threshold to validate.
     *
     *
     * @throws InvalidArgumentException If the threshold is not between 0 and 100.
     */
    protected function isProperThreshold(float $threshold) : void
    {
        if ($threshold < 0 || $threshold > 100) {
            throw new InvalidArgumentException(message: 'Threshold must be between 0 and 100.');
        }
    }

    /**
     * Searches for items with a similarity percentage above a specified threshold.
     *
     * This method uses the similarity percentage between the query and each item to determine matches.
     *
     * @param string      $query     The search query string.
     * @param float       $threshold The minimum similarity percentage (0 to 100).
     * @param string|null $key       The key to search within if items are associative arrays.
     *
     * @return static A new instance containing the matched items.
     *
     * @throws InvalidArgumentException If the threshold is not between 0 and 100.
     *
     * @example
     * $arrh = new Arrhae(['apple', 'aple', 'apply', 'banana']);
     * $results = $arrh->similaritySearch('apple', 80);
     * // Returns ['apple', 'aple', 'apply']
     *
     * $arrhAssoc = new Arrhae([
     *     ['title' => 'Introduction to PHP'],
     *     ['title' => 'Advanced PHP Techniques'],
     *     ['title' => 'PHP for Beginners'],
     *     ['title' => 'JavaScript Essentials'],
     * ]);
     * $resultsAssoc = $arrhAssoc->similaritySearch('PHP Intro', 70, 'title');
     * // Returns [
     * //     ['title' => 'Introduction to PHP'],
     * //     ['title' => 'PHP for Beginners'],
     * // ]
     */
    public function similaritySearch(string $query, float $threshold = 70.0, string|null $key = null) : static
    {
        $this->isProperThreshold($threshold);

        $fuzz    = new Fuzz();
        $process = new Process(fuzz: $fuzz);

        $matchedItems = array_filter(
            $this->getItems(),
            function ($item) use ($key, $fuzz, $query, $threshold) : bool {
                $target = $key !== null ? ($item[$key] ?? '') : $item;

                if (! is_string($target)) {
                    return false;
                }

                $similarity = $fuzz->ratio(s1: strtolower($query), s2: strtolower($target));

                return $similarity >= $threshold;
            }
        );

        return new static(items: array_values($matchedItems));
    }

    /**
     * Performs a Levenshtein-based search and sorts the results by similarity.
     *
     * This method finds items within a certain distance from the query and sorts them by their similarity to the query.
     *
     * @param string      $query       The search query string.
     * @param int         $maxDistance The maximum Levenshtein distance allowed.
     * @param string|null $key         The key to search within if items are associative arrays.
     *
     * @return static A new instance containing the matched items sorted by similarity.
     *
     * @throws InvalidArgumentException If the max distance is negative.
     *
     * @example
     * $arrh = new Arrhae(['apple', 'apricot', 'banana', 'grape', 'pineapple']);
     * $results = $arrh->levenshteinSearch('appel', 2);
     * // Returns ['apple']
     *
     * $arrhAssoc = new Arrhae([
     *     ['name' => 'Alice'],
     *     ['name' => 'Alicia'],
     *     ['name' => 'Alina'],
     *     ['name' => 'Bob'],
     * ]);
     * $resultsAssoc = $arrhAssoc->levenshteinSearch('Alic', 2, 'name');
     * // Returns [
     * //     ['name' => 'Alice'],
     * //     ['name' => 'Alicia'],
     * // ]
     */
    public function levenshteinSearch(string $query, int|null $maxDistance = null, string|null $key = null) : static
    {
        $maxDistance ??= 2;
        if ($maxDistance < 0) {
            throw new InvalidArgumentException(message: 'Maximum distance cannot be negative.');
        }

        $matchedItems = [];

        foreach ($this->getItems() as $item) {
            $target = $key !== null ? ($item[$key] ?? '') : $item;

            if (! is_string($target)) {
                continue;
            }

            $distance = levenshtein(strtolower($query), strtolower($target));

            if ($distance <= $maxDistance) {
                $matchedItems[$distance][] = $item;
            }
        }

        ksort($matchedItems);

        // Flatten the array while preserving order
        $sortedMatchedItems = [];
        foreach ($matchedItems as $matchedItem) {
            foreach ($matchedItem as $item) {
                $sortedMatchedItems[] = $item;
            }
        }

        return new static(items: $sortedMatchedItems);
    }

    /**
     * Performs a partial match on the collection items based on a given query.
     *
     * This method checks if the query string is a substring of the target string.
     *
     * @param string      $query The search query string.
     * @param string|null $key   The key to search within if items are associative arrays.
     *
     * @return static A new instance containing the matched items.
     *
     * @example
     * $arrh = new Arrhae(['apple', 'banana', 'apricot', 'grape']);
     * $results = $arrh->partialMatch('app');
     * // Returns ['apple', 'apricot']
     *
     * $arrhAssoc = new Arrhae([
     *     ['name' => 'Alice'],
     *     ['name' => 'Alicia'],
     *     ['name' => 'Alina'],
     *     ['name' => 'Bob'],
     * ]);
     * $resultsAssoc = $arrhAssoc->partialMatch('Ali', 'name');
     * // Returns [
     * //     ['name' => 'Alice'],
     * //     ['name' => 'Alicia'],
     * // ]
     */
    public function partialMatch(string $query, string|null $key = null) : static
    {
        $matchedItems = array_filter(
            $this->getItems(),
            static function ($item) use ($key, $query) : bool {
                $target = $key !== null ? ($item[$key] ?? '') : $item;

                if (! is_string($target)) {
                    return false;
                }

                // Check if query is a substring of target
                return stripos($target, $query) !== false;
            }
        );

        return new static(items: array_values($matchedItems));
    }

    /**
     * Performs a token sort match on the collection items based on a given query.
     *
     * This method sorts the tokens in both the query and target strings and then calculates the similarity ratio.
     *
     * @param string      $query     The search query string.
     * @param float|null  $threshold The minimum similarity percentage (0 to 100) required for a match.
     * @param string|null $key       The key to search within if items are associative arrays.
     *
     * @return static A new instance containing the matched items.
     *
     * @example
     * $arrh = new Arrhae(['apple banana', 'banana apple', 'apple grape', 'banana grape']);
     * $results = $arrh->tokenSortMatch('banana apple', 90);
     * // Returns ['apple banana', 'banana apple']
     */
    public function tokenSortMatch(string $query, float|null $threshold = null, string|null $key = null) : static
    {
        $threshold ??= 70.0;
        $this->isProperThreshold($threshold);

        $fuzz    = new Fuzz();
        $process = new Process(fuzz: $fuzz);

        // Sort tokens in the query
        $sortedQuery = $this->sortTokens($query);

        $matchedItems = array_filter(
            $this->getItems(),
            function ($item) use ($key, $fuzz, $sortedQuery, $threshold) : bool {
                $target = $key !== null ? ($item[$key] ?? '') : $item;

                if (! is_string($target)) {
                    return false;
                }

                // Sort tokens in the target
                $sortedTarget = $this->sortTokens($target);

                // Calculating similarity using FuzzyWuzzy
                $similarity = $fuzz->ratio(s1: $sortedQuery, s2: $sortedTarget);

                return $similarity >= $threshold;
            }
        );

        return new static(items: array_values($matchedItems));
    }

    /**
     * Helper method to sort tokens in a string.
     *
     * @param string $string The string to sort tokens for.
     *
     * @return string The string with sorted tokens.
     */
    protected function sortTokens(string $string) : string
    {
        $tokens = explode(' ', strtolower($string));
        sort($tokens); // Sort tokens in ascending order

        return implode(' ', $tokens);
    }

    /**
     * Performs a token set match on the collection items based on a given query.
     *
     * This method calculates the similarity ratio between the unique tokens of the query and target strings.
     *
     * @param string      $query     The search query string.
     * @param float       $threshold The minimum similarity percentage (0 to 100) required for a match.
     * @param string|null $key       The key to search within if items are associative arrays.
     *
     * @return static A new instance containing the matched items.
     *
     * @throws InvalidArgumentException If the threshold is not between 0 and 100.
     *
     * @example
     * $arrh = new Arrhae(['apple banana', 'banana apple', 'apple grape', 'banana grape']);
     * $results = $arrh->tokenSetMatch('apple banana', 90);
     * // Returns ['apple banana', 'banana apple']
     */
    public function tokenSetMatch(string $query, float|null $threshold = null, string|null $key = null) : static
    {
        $threshold ??= 70.0;
        $this->isProperThreshold($threshold);

        // Initializing FuzzyWuzzy components
        $fuzz    = new Fuzz();
        $process = new Process(fuzz: $fuzz);

        // Get unique tokens in the query
        $uniqueQueryTokens = array_unique(explode(' ', strtolower($query)));
        sort($uniqueQueryTokens);
        $sortedQuery = implode(' ', $uniqueQueryTokens);

        $matchedItems = array_filter(
            $this->getItems(),
            function ($item) use ($key, $fuzz, $sortedQuery, $threshold, $process) : bool {
                $target = $key !== null ? ($item[$key] ?? '') : $item;

                if (! is_string($target)) {
                    return false;
                }

                // Get unique tokens in the target
                $uniqueTargetTokens = array_unique(explode(' ', strtolower($target)));
                sort($uniqueTargetTokens);
                $sortedTarget = implode(' ', $uniqueTargetTokens);

                // Calculating similarity using FuzzyWuzzy
                $similarity = $fuzz->ratio(s1: $sortedQuery, s2: $sortedTarget);

                return $similarity >= $threshold;
            }
        );

        return new static(items: array_values($matchedItems));
    }

    /**
     * Performs a phonetic match on the collection items based on a given query.
     *
     * This method uses the Metaphone algorithm to find phonetically similar strings.
     *
     * @param string      $query The search query string.
     * @param string|null $key   The key to search within if items are associative arrays.
     *
     * @return static A new instance containing the matched items.
     *
     * @example
     * $arrh = new Arrhae(['Smith', 'Smyth', 'Smithe', 'Johnson']);
     * $results = $arrh->phoneticMatch('Smyth');
     * // Returns ['Smith', 'Smyth', 'Smithe']
     */
    public function phoneticMatch(string $query, string|null $key = null) : static
    {
        $queryPhonetic = metaphone(strtolower($query));

        $matchedItems = array_filter(
            $this->getItems(),
            static function ($item) use ($key, $queryPhonetic) : bool {
                $target = $key !== null ? ($item[$key] ?? '') : $item;

                if (! is_string($target)) {
                    return false;
                }

                // Calculate phonetic code
                $targetPhonetic = metaphone(strtolower($target));

                return $queryPhonetic === $targetPhonetic;
            }
        );

        return new static(items: array_values($matchedItems));
    }

    /**
     * Performs a regular expression search on the collection items based on a given pattern.
     *
     * This method returns items that match the provided regular expression pattern.
     *
     * @param string      $pattern The regular expression pattern.
     * @param string|null $key     The key to search within if items are associative arrays.
     *
     * @return static A new instance containing the matched items.
     *
     * @throws InvalidArgumentException If the provided pattern is invalid.
     *
     * @example
     * $arrh = new Arrhae(['apple', 'banana', 'apricot', 'grape']);
     * $results = $arrh->regexSearch('/^ap/');
     * // Returns ['apple', 'apricot']
     *
     * $arrhAssoc = new Arrhae([
     *     ['name' => 'Alice'],
     *     ['name' => 'Alicia'],
     *     ['name' => 'Alina'],
     *     ['name' => 'Bob'],
     * ]);
     * $resultsAssoc = $arrhAssoc->regexSearch('/^Ali/', 'name');
     * // Returns [
     * //     ['name' => 'Alice'],
     * //     ['name' => 'Alicia'],
     * // ]
     */
    public function regexSearch(string $pattern, string|null $key = null) : static
    {
        if (@preg_match($pattern, '') === false) {
            throw new InvalidArgumentException(message: 'Invalid regular expression pattern.');
        }

        $matchedItems = array_filter(
            $this->getItems(),
            static function ($item) use ($key, $pattern) : bool {
                $target = $key !== null ? ($item[$key] ?? '') : $item;

                if (! is_string($target)) {
                    return false;
                }

                return preg_match($pattern, $target) === 1;
            }
        );

        return new static(items: array_values($matchedItems));
    }

    /**
     * Performs a custom match on the collection items using a user-defined callback.
     *
     * This method allows for highly flexible and customizable search criteria.
     *
     * @param callable    $callback The callback function to determine a match. Should return a boolean.
     * @param string|null $key      The key to search within if items are associative arrays.
     *
     * @return static A new instance containing the matched items.
     *
     * @example
     * $arrh = new Arrhae(['apple', 'banana', 'apricot', 'grape']);
     * $results = $arrh->customMatch(function($item) {
     *     return strpos($item, 'ap') === 0;
     * });
     * // Returns ['apple', 'apricot']
     *
     * $arrhAssoc = new Arrhae([
     *     ['name' => 'Alice', 'age' => 30],
     *     ['name' => 'Alicia', 'age' => 25],
     *     ['name' => 'Alina', 'age' => 28],
     *     ['name' => 'Bob', 'age' => 35],
     * ]);
     * $resultsAssoc = $arrhAssoc->customMatch(function($value, $item) {
     *     return $item['age'] > 27;
     * }, 'age');
     * // Returns [
     * //     ['name' => 'Alice', 'age' => 30],
     * //     ['name' => 'Alina', 'age' => 28],
     * // ]
     */
    public function customMatch(callable $callback, string|null $key = null) : static
    {
        $matchedItems = array_filter(
            $this->getItems(),
            static function ($item) use ($key, $callback) {
                $target = $key !== null ? ($item[$key] ?? null) : $item;

                return $callback($target, $item);
            }
        );

        return new static(items: array_values($matchedItems));
    }

    /**
     * Sorts the matched items by similarity in descending order.
     *
     * This method is typically used after a search to rank the results based on how closely they match the query.
     *
     * @param string      $query The search query string.
     * @param string|null $key   The key to search within if items are associative arrays.
     *
     * @return array An array of matched items sorted by similarity.
     *
     * @example
     * $arrh = new Arrhae(['apple', 'aple', 'apply', 'banana']);
     * $results = $arrh->fuzzyMatch('apple', 60, 'name')->sortBySimilarity('apple', 'name');
     * // Returns items sorted by similarity to 'apple'
     */
    public function sortBySimilarity(string $query, string|null $key = null) : array
    {
        $queryLower = strtolower($query);

        $sortedItems = $this->getItems();
        usort($sortedItems, function ($a, $b) use ($key, $queryLower) : int {
            $fuzz   = new Fuzz();
            $aValue = $key !== null ? ($a[$key] ?? '') : $a;
            $bValue = $key !== null ? ($b[$key] ?? '') : $b;

            if (! is_string($aValue) || ! is_string($bValue)) {
                return 0;
            }

            $similarityA = $fuzz->ratio(s1: $queryLower, s2: strtolower($aValue));
            $similarityB = $fuzz->ratio(s1: $queryLower, s2: strtolower($bValue));

            return $similarityB <=> $similarityA;
        });

        return $sortedItems;
    }
}
