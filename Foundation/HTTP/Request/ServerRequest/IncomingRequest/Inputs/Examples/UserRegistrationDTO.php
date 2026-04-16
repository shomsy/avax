<?php

declare(strict_types=1);

namespace Avax\HTTP\Request\ServerRequest\IncomingRequest\Inputs\Examples;

use Avax\DataHandling\ObjectHandling\DTO\AbstractDTO;
use Avax\DataHandling\Validation\Attributes\Rules\Email;
use Avax\DataHandling\Validation\Attributes\Rules\Required;
use Avax\DataHandling\Validation\Attributes\Rules\Min;
use Avax\DataHandling\Validation\Attributes\Rules\Max;
use Avax\DataHandling\Validation\Attributes\Rules\RegexException;

/**
 * UserRegistrationDTO - Example of validated Request DTO.
 *
 * Demonstrates declarative validation through PHP 8 attributes.
 * Validation runs automatically during hydration from request inputs.
 *
 * Usage:
 * ```php
 * $dto = $request->inputs()->as(UserRegistrationDTO::class);
 * // Automatically validates all fields
 * ```
 */
class UserRegistrationDTO extends AbstractDTO
{
    #[Required]
    #[Min(2)]
    #[Max(100)]
    public string $name;

    #[Required]
    #[Email]
    public string $email;

    #[Required]
    #[Min(8)]
    #[RegexException(
        pattern: '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/',
        message: 'Password must contain uppercase, lowercase and number'
    )]
    public string $password;

    #[Min(18)]
    #[Max(120)]
    public int $age = 18;

    public ?string $phone = null;
}
