<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\URI\System\System\Capabilities\Parts;

use Stringable;

final readonly class Fragment implements Stringable
{
    private string $fragment;

    public function __construct(string $fragment)
    {
        $this->fragment = ltrim($fragment, '#');
    }

    public function __toString() : string
    {
        return $this->fragment;
    }
}
