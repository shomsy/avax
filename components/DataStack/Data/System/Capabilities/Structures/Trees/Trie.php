<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Capabilities\Structures\Trees;

use Countable;
use Override;

final readonly class Trie implements Countable
{
    /**
     * @param array<string, Trie> $children
     */
    private function __construct(private array $children = [], private bool $word = false) {}

    public function insert(string $word) : self
    {
        if ($word === '') {
            return new self(children: $this->children, word: true);
        }

        $letter            = $word[0];
        $rest              = substr(string: $word, offset: 1);
        $children          = $this->children;
        $children[$letter] = ($children[$letter] ?? self::empty())->insert(word: $rest);

        return new self(children: $children, word: $this->word);
    }

    public static function empty() : self
    {
        return new self();
    }

    public function contains(string $word) : bool
    {
        if ($word === '') {
            return $this->word;
        }

        $letter = $word[0];

        if (! isset($this->children[$letter])) {
            return false;
        }

        return $this->children[$letter]->contains(word: substr(string: $word, offset: 1));
    }

    public function hasPrefix(string $prefix) : bool
    {
        if ($prefix === '') {
            return true;
        }

        $letter = $prefix[0];

        if (! isset($this->children[$letter])) {
            return false;
        }

        return $this->children[$letter]->hasPrefix(prefix: substr(string: $prefix, offset: 1));
    }

    #[Override]
    public function count() : int
    {
        $count = $this->word ? 1 : 0;

        foreach ($this->children as $child) {
            $count += $child->count();
        }

        return $count;
    }
}
