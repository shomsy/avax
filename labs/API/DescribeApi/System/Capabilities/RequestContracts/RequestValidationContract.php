<?php

declare(strict_types=1);

namespace Avax\Labs\API\DescribeApi\System\Capabilities\RequestContracts;

interface RequestValidationContract
{
    public function validate(mixed $data): RequestValidationResult;
}
