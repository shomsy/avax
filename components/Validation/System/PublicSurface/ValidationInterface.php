<?php

declare(strict_types=1);

namespace Avax\Components\Validation\System\PublicSurface;

interface ValidationInterface
{
    public function validate(array $data, array $rules): ValidationResult;
}