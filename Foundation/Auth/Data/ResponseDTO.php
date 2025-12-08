<?php

declare(strict_types=1);

namespace Avax\Auth\Data;

use Avax\DataHandling\ObjectHandling\DTO\AbstractDTO;

class ResponseDTO extends AbstractDTO
{
    /**
     * AccessControl ID.
     */
    public int $id;

    /**
     * AccessControl email.
     */
    public string $email;

    /**
     * AccessControl username.
     */
    public string $username;

    /**
     * AccessControl roles.
     * @var array<string, mixed>|null
     */
    public array|null $roles = null;
}
