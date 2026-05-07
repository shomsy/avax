<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\PublicSurface;

use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\User as UserEntity;

class UserRecord
{
    public function __construct(
        public readonly string  $id,
        public readonly string  $email,
        public readonly ?string $name = null,
    ) {}

    public static function fromEntity(UserEntity $userEntity) : self
    {
        return new self(
            id   : $userEntity->id->value,
            email: $userEntity->email->value,
            name : $userEntity->name?->value,
        );
    }
}
