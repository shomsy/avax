<?php

declare(strict_types=1);

namespace Avax\Components\Application\Validation\System\PublicSurface;

use Avax\Components\Application\Validation\System\Capabilities\Rules\ValidationFailure;
use Avax\Components\Application\Validation\System\Capabilities\Rules\Validator;
use Avax\Components\Application\Validation\System\Flows\ValidateData\ValidateData;

final class Validation implements ValidationInterface
{
    private ValidateData $flow;

    public function __construct()
    {
        $this->flow = new ValidateData();
    }

    public function validate(array $data, array $rules, array $messages = []) : ValidationFailure
    {
        return $this->flow->execute($data, $rules, $messages);
    }

    public function validateOrFail(array $data, array $rules, array $messages = []) : array
    {
        return $this->flow->executeOrFail($data, $rules, $messages);
    }

    public static function make(array $data, array $rules, array $messages = []) : ValidationFailure
    {
        return Validator::make($data, $rules, $messages);
    }
}
