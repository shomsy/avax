<?php

declare(strict_types=1);

namespace Avax\Components\API\Surface\System\Capabilities\Rpc;

final readonly class RpcRequestValidator
{
    /**
     * @param array<string, mixed> $request
     */
    public function isValid(array $request) : bool
    {
        return $this->validate($request) === [];
    }

    /**
     * @param array<string, mixed> $request
     */
    public function validate(array $request) : array
    {
        $errors = [];

        if (! isset($request['method']) || ! is_string($request['method'])) {
            $errors[] = 'Missing or invalid "method" field.';
        }

        if (isset($request['params']) && ! is_array($request['params'])) {
            $errors[] = '"params" must be an object.';
        }

        return $errors;
    }
}
