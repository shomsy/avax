<?php

declare(strict_types=1);

namespace Avax\HTTP\Request\ServerRequest\IncomingRequest\Inputs\Examples;

use Avax\DataHandling\ObjectHandling\DTO\AbstractDTO;
use Avax\DataHandling\Validation\Attributes\Rules\Required;
use Avax\DataHandling\Validation\Attributes\Rules\Email;
use Avax\DataHandling\Validation\Attributes\Rules\Min;

/**
 * LoginDTO - Example of validated Login Request DTO.
 *
 * Usage:
 * ```php
 * $dto = $request->inputs()->as(LoginDTO::class);
 * ```
 */
class LoginDTO extends AbstractDTO
{
    #[Required]
    #[Email]
    public string $email;

    #[Required]
    #[Min(min: 1)]
    public string $password;

    public bool $remember_me = false;
}
