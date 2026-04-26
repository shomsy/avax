<?php

declare(strict_types=1);

namespace Avax\HTTP\URI\Parts;

use Stringable;

/**
 * Represents a URI fragment.
 */
final readonly class Fragment implements Stringable
{
    private string $fragment;

    public function __construct(string $fragment)
    {
        $this->fragment = rawurlencode($fragment);
    }

    public function __toString() : string
    {
        return $this->fragment;
    }
}