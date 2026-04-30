<?php

declare(strict_types=1);

namespace Avax\Components\Application\Validation\System\PublicSurface;

use Avax\Components\Application\Validation\System\Capabilities\Rules\ValidationFailure;
use Avax\Components\Application\Validation\System\Capabilities\Rules\Validator;
use Avax\Components\Application\Validation\System\Flows\ValidateData\ValidateData;
use Override;

final readonly class Validation implements ValidationInterface
{
    private ValidateData $validateData;

    public function __construct()
    {
        $this->validateData = new ValidateData();
    }

    #[Override]
    public function validate(array $data, array $rules, array $messages = []) : ValidationFailure
    {
        return $this->validateData->execute($data, $rules, $messages);
    }

    public function validateOrFail(array $data, array $rules, array $messages = []) : array
    {
        return $this->validateData->executeOrFail($data, $rules, $messages);
    }

    public static function make(array $data, array $rules, array $messages = []) : ValidationFailure
    {
        return Validator::make($data, $rules, $messages);
    }
}
