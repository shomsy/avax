<?php

declare(strict_types=1);

namespace Avax\Components\API\ApiBlueprint\System\Foundation\Failure;

use RuntimeException;

final class ApiBlueprintInvalid extends RuntimeException
{
    public function __construct(
        string  $field,
        ?string $reason = null,
    )
    {
        parent::__construct(
            $reason !== null
                ? "API blueprint invalid: {$field} - {$reason}"
                : "API blueprint invalid: {$field}"
        );
    }
}
