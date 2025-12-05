<?php

declare(strict_types=1);

namespace Gemini\Auth\Actions;

use Gemini\Auth\Contracts\IdentityInterface;
use Gemini\Auth\Contracts\UserInterface;

final readonly class GetUser
{
    public function __construct(private IdentityInterface $identity) {}

    public function execute(): ?UserInterface
    {
        return $this->identity->user();
    }
}
