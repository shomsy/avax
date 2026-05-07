<?php

declare(strict_types=1);

namespace Avax\Components\API\Surface\System\Flows\HandleRpcRequest;

use Avax\Components\API\Surface\System\Capabilities\Rpc\RpcExecutor;
use Avax\Components\API\Surface\System\Capabilities\Rpc\RpcMethodRegistry;
use Avax\Components\API\Surface\System\Capabilities\Rpc\RpcRequestValidator;

final readonly class HandleRpcRequest
{
    public function handle(RpcMethodRegistry $registry, array $request) : array
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

        $executor = new RpcExecutor($registry);

        return $executor->execute(
            id    : $request['id'] ?? null,
            method: $request['method'],
            params: $request['params'] ?? [],
        );
    }
}
