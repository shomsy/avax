<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Configuration\Graphs;

use Avax\Components\Identity\Flows\AuthorizeAction\AuthorizeAction;

final readonly class AuthorizationGraph
{
    public function __construct(private AuthorizeAction $authorizeAction) {}

    public function authorizeAction(): AuthorizeAction
    {
        return $this->authorizeAction;
    }
}
