<?php

declare(strict_types=1);

namespace Avax\DataHandling\DataTransfer\Capabilities\FieldValidation;

use Avax\DataHandling\DataTransfer\Capabilities\ErrorReporting\DataTransferViolation;
use Avax\DataHandling\DataTransfer\Foundation\FieldPath;
use Avax\DataHandling\DataTransfer\InspectDataShape\DataField;

interface ValidationRuleInterface
{
    public function validate(mixed $value, DataField $field, FieldPath $path) : DataTransferViolation|null;
}
