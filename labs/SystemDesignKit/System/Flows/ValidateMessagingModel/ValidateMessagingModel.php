<?php

declare(strict_types=1);

namespace Avax\Labs\SystemDesignKit\System\Flows\ValidateMessagingModel;

use Avax\Labs\SystemDesignKit\System\Capabilities\Messaging\MessagingModel;

/**
 * Validates a messaging model configuration.
 *
 * @experimental V3 labs
 */
final class ValidateMessagingModel
{
    /**
     * @param array<int|string, mixed> $config
     *
     * @return array{valid: bool, errors: list<string>, model: ?MessagingModel}
     */
    public function execute(array $config) : array
    {
        $model  = MessagingModel::fromConfig($config);
        $result = $model->validate();

        return [
            'valid'  => $result['valid'],
            'errors' => $result['errors'],
            'model'  => $result['valid'] ? $model : null,
        ];
    }
}
