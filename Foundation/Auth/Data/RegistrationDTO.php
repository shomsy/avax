<?php

declare(strict_types=1);

namespace Avax\Auth\Data;

use Avax\DataHandling\ObjectHandling\DTO\AbstractDTO;
use Avax\DataHandling\Validation\Attributes\Rules\AlphaNum;
use Avax\DataHandling\Validation\Attributes\Rules\AlphaNumOrEmail;
use Avax\DataHandling\Validation\Attributes\Rules\Max;
use Avax\DataHandling\Validation\Attributes\Rules\Min;
use Avax\DataHandling\Validation\Attributes\Rules\RegexException;
use Avax\DataHandling\Validation\Attributes\Rules\Required;
use Avax\DataHandling\Validation\Attributes\Rules\StringType;

class RegistrationDTO extends AbstractDTO
{
    #[Required]
    #[StringType]
    #[AlphaNumOrEmail]
    public string $email;

    #[Required]
    #[StringType]
    #[Min(min: 3)]
    #[AlphaNum]
    public string $username;

    #[Required]
    #[StringType]
    #[Min(min: 8)]
    #[Max(max: 64)]
    #[RegexException(pattern: "/^(?=.*[A-Za-z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/")]
    public string $password;

    #[Required]
    #[StringType]
    #[AlphaNum]
    #[Max(max: 50)]
    // Map 'first_name' from array to this property if hydrator supports snake_case mapping,
    // otherwise we might need attributes or keep it snake_case.
    // Assuming framework handles mapping or array keys match property names.
    // Based on step 788, the old code used $first_name. I will strict to standard camelCase for properties
    // but the input array likely has snake_case keys. DTOs usually handle this via Attributes or Mapper.
    // BUT the previous implementation had $first_name.
    // I will use $firstName to be PSR-12 compliant, assuming AbstractDTO handles hydration logic (snake->camel).
    public string $firstName;

    #[Required]
    #[StringType]
    #[AlphaNum]
    #[Max(max: 50)]
    public string $lastName;

    #[Required]
    public bool $isAdmin = false;
}
