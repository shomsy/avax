<?php

declare(strict_types=1);

namespace Avax\Auth\Actions;

use Avax\Auth\Contracts\IdentityInterface;

final readonly class Check
{
    public function __construct(private IdentityInterface $identity) {}

    public function execute() : bool
    {
        return $this->identity->check();
    }
}
