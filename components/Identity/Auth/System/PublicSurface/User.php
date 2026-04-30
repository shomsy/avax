<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\PublicSurface;

use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\User as UserEntity;

class User
{
    public function __construct(
        public readonly string $id,
        public readonly string $email,
        public readonly ?string $name = null,
    ) {}

    public static function fromEntity(UserEntity $entity): self
    {
        return new self(
            id: $entity->id->value,
            email: $entity->email->value,
            name: $entity->name?->value,
        );
    }
}
