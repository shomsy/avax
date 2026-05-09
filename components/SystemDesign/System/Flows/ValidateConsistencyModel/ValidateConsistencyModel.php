<?php

declare(strict_types=1);

namespace Avax\Components\SystemDesign\System\Flows\ValidateConsistencyModel;

use Avax\Components\SystemDesign\System\Capabilities\Consistency\ConsistencyModel;

/**
 * Validates a consistency model configuration.
 *
 * @experimental V3 labs
 */
final class ValidateConsistencyModel
{
    /**
     * @param array<int|string, mixed> $config
     *
     * @return array{valid: bool, errors: list<string>, model: ?ConsistencyModel}
     */
    public function execute(array $config) : array
    {
        $model  = ConsistencyModel::fromConfig($config);
        $result = $model->validate();

        return [
            'valid'  => $result['valid'],
            'errors' => $result['errors'],
            'model'  => $result['valid'] ? $model : null,
        ];
    }
}
