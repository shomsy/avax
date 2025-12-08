<?php

declare(strict_types=1);

namespace Avax\Auth\Actions;

use Avax\Auth\Contracts\IdentityInterface;
use Avax\Auth\Contracts\UserInterface;

final readonly class GetUser
{
    public function __construct(private IdentityInterface $identity) {}

    public function execute(): UserInterface|null
    {
        return $this->identity->user();
    }
}
