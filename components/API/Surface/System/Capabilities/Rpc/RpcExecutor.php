<?php

declare(strict_types=1);

namespace Avax\Components\API\Surface\System\Capabilities\Rpc;

use Throwable;

final readonly class RpcExecutor
{
    public function __construct(
        private RpcMethods $methods,
    ) {}

    /**
     * @param array<string, mixed> $params
     *
     * @return array{id: mixed, result: mixed, error: null|array{message: string, code: int|string}}
     */
    public function execute(mixed $id, string $method, array $params = []) : array
    {
        try {
            $result = $this->methods->call($method, $params);

            return [
                'id'     => $id,
                'result' => $result,
                'error'  => null,
            ];
        } catch (Throwable $e) {
            return [
                'id'     => $id,
                'result' => null,
                'error'  => [
                    'message' => $e->getMessage(),
                    'code'    => $e->getCode(),
                ],
            ];
        }
    }
}
