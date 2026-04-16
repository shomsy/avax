<?php

declare(strict_types=1);

namespace Avax\HTTP\Request\ServerRequest\IncomingRequest\Inputs\Examples;

use Avax\DataHandling\Validation\Attributes\Rules\Integer;
use Avax\DataHandling\Validation\Attributes\Rules\Max;
use Avax\DataHandling\Validation\Attributes\Rules\Min;
use Avax\DataHandling\Validation\Attributes\Rules\Required;
use Avax\DataHandling\Validation\Attributes\Rules\StringType;
use Avax\HTTP\Request\Request;

/**
 * CreateNewProjectRequest - Example of Laravel-style Form Request.
 *
 * Demonstrates declarative validation through PHP 8 attributes.
 * Validation runs automatically during hydration from ServerRequest.
 *
 * Usage:
 * ```php
 * $request = CreateNewProjectRequest::fromRequest($httpRequest);
 * $request->projectName; // already validated!
 * ```
 */
class CreateNewProjectRequest extends Request
{
    #[Required]
    #[StringType]
    #[Min(3)]
    #[Max(100)]
    public string $projectName;

    #[Required]
    #[Integer]
    #[Min(1)]
    public int $projectNumber;

    #[Min(0)]
    public int $priority = 0;
}
