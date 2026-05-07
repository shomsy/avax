<?php

declare(strict_types=1);

namespace Avax\Components\API\OpenAPI\System\PublicSurface;

final readonly class OpenApiValidationReport
{
    /**
     * @param list<string> $errors
     */
    public function __construct(public array $errors = []) {}

    public function isValid() : bool
    {
        return $this->errors === [];
    }
}
