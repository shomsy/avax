<?php

declare(strict_types=1);

namespace Avax\Components\Application\Validation\System\Flows\ValidateData;

use Avax\Components\Application\Validation\System\Capabilities\Rules\ValidationFailure;
use Avax\Components\Application\Validation\System\Capabilities\Rules\Validator;
use InvalidArgumentException;

/**
 * ValidateData flow - orchestrates the validation process.
 */
final class ValidateData
{
    /**
     * Validate and throw exception on failure.
     *
     * @throws InvalidArgumentException
     */
    public function executeOrFail(array $data, array $rules, array $messages = []) : array
    {
        $result = $this->execute($data, $rules, $messages);

        if ($result->fails()) {
            throw new InvalidArgumentException($result->first() ?? 'Validation failed');
        }

        return $data;
    }

    public function execute(array $data, array $rules, array $messages = []) : ValidationFailure
    {
        return Validator::make($data, $rules, $messages);
    }
}
