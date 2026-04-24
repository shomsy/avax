<?php

declare(strict_types=1);

namespace Gemini\Auth\DTO;

use Gemini\DataHandling\ObjectHandling\DTO\AbstractDTO;

/**
 * Class ResponseDTO
 *
 * This class extends AbstractDTO to leverage its automatic property
 * validation and assignment mechanism. It's specifically designed to
 * handle user response data. By extending AbstractDTO, we ensure
 * consistency and reduce boilerplate validation code.
 */
class ResponseDTO extends AbstractDTO
{
    /**
     * AccessControl ID, must be an integer.
     */
    public int $id;

    /**
     * AccessControl email, must be a valid string format.
     */
    public string $email;

    /**
     * AccessControl username, a string identifier for the user.
     */
    public string $username;

    /**
     * AccessControl roles, an array to hold roles or null if no roles are assigned.
     * Nullable type written in longer format for clarity.
     */
    public array|null $roles = null;
}
