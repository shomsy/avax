<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Request\ServerRequest\IncomingRequest\RequestedInputs\Inputs\Examples;

use Avax\Components\Application\Validation\System\Capabilities\Metadata\Attributes\Email;
use Avax\Components\Application\Validation\System\Capabilities\Metadata\Attributes\Min;
use Avax\Components\Application\Validation\System\Capabilities\Metadata\Attributes\MinLength;
use Avax\Components\Application\Validation\System\Capabilities\Metadata\Attributes\PasswordComplexity;
use Avax\Components\Application\Validation\System\Capabilities\Metadata\Attributes\Required;
use Avax\Components\DataStack\Data\System\Capabilities\DataTransfer\Foundation\AbstractDTO;

final class UserRegistrationDTO extends AbstractDTO
{
    #[Required]
    #[MinLength(length: 2)]
    public string $name;

    #[Required]
    #[Email]
    public string $email;

    #[Required]
    #[MinLength(length: 8)]
    #[PasswordComplexity]
    public string $password;

    #[Min(minimum: 18)]
    public int $age = 18;

    public string|null $phone = null;
}
