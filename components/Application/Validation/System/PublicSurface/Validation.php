<?php

declare(strict_types=1);

namespace Avax\Components\Application\Validation\System\PublicSurface;

final class Validation implements ValidationInterface
{
    public function validate(array $data, array $rules) : ValidationResult
    {
        return new ValidationResult(errors: []);
    }
}