<?php

declare(strict_types=1);

namespace Avax\Components\API\Surface\System\Capabilities\RequestSchemas;

interface RequestPayloadValidator
{
    public function validate(mixed $data) : RequestValidationResult;
}
