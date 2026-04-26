<?php

declare(strict_types=1);

namespace Avax\DataFoundation\DataTransfer\Capabilities\FieldValidation;

use Avax\DataFoundation\DataTransfer\Capabilities\ErrorReporting\DataTransferViolation;
use Avax\DataFoundation\DataTransfer\Foundation\FieldPath;
use Avax\DataFoundation\DataTransfer\InspectDataShape\DataField;

interface ValidationRuleInterface
{
    public function validate(mixed $value, DataField $field, FieldPath $path) : DataTransferViolation|null;
}
