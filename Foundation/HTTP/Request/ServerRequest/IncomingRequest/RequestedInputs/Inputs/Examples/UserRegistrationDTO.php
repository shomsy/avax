<?php

declare(strict_types=1);

namespace Avax\HTTP\Request\ServerRequest\IncomingRequest\RequestedInputs\Inputs\Examples;

use Avax\DataHandling\ObjectHandling\DTO\AbstractDTO;
use Avax\DataHandling\Validation\Attributes\Required;
use Avax\DataHandling\Validation\Attributes\Rules\EmailRule;
use Avax\DataHandling\Validation\Attributes\Rules\MinLengthRule;
use Avax\DataHandling\Validation\Attributes\Rules\MinRule;
use Avax\DataHandling\Validation\Attributes\Rules\PasswordComplexityRule;

final class UserRegistrationDTO extends AbstractDTO
{
    #[Required]
    #[MinLengthRule(length: 2)]
    public string $name;

    #[Required]
    #[EmailRule]
    public string $email;

    #[Required]
    #[MinLengthRule(length: 8)]
    #[PasswordComplexityRule]
    public string $password;

    #[MinRule(minimum: 18)]
    public int $age = 18;

    public string|null $phone = null;
}
