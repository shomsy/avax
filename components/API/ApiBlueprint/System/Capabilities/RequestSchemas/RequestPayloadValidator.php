<?php

declare(strict_types=1);

namespace Avax\Components\API\ApiBlueprint\System\Capabilities\RequestSchemas;

interface RequestPayloadValidator
{
    public function validate(mixed $data) : RequestValidationResult;
}
