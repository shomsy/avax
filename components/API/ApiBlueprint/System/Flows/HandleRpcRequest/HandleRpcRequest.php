<?php

declare(strict_types=1);

namespace Avax\Components\API\ApiBlueprint\System\Flows\HandleRpcRequest;

use Avax\Components\API\ApiBlueprint\System\Capabilities\Rpc\RpcExecutor;
use Avax\Components\API\ApiBlueprint\System\Capabilities\Rpc\RpcMethods;
use Avax\Components\API\ApiBlueprint\System\Capabilities\Rpc\RpcRequestValidator;

final readonly class HandleRpcRequest
{
    /**
     * @param array<string, mixed> $request
     *
     * @return array<string, mixed>
     */
    public function handle(RpcMethods $methods, array $request) : array
    {
        $validator = new RpcRequestValidator();

        if (! $validator->isValid($request)) {
            return [
                'id'     => $request['id'] ?? null,
                'result' => null,
                'error'  => [
                    'message' => 'Invalid request.',
                    'errors'  => $validator->validate($request),
                ],
            ];
        }

        $executor = new RpcExecutor($methods);

        return $executor->execute(
            id    : $request['id'] ?? null,
            method: $request['method'],
            params: $request['params'] ?? [],
        );
    }
}
