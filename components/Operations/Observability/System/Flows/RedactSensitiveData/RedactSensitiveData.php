<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Observability\System\Flows\RedactSensitiveData;

use Avax\Components\Operations\Observability\System\Capabilities\Drivers\Redactor;

final readonly class RedactSensitiveData
{
    /**
     * @param array<string, mixed> $data
     *
     * @return array<string, mixed>
     */
    public function redact(Redactor $redactor, array $data) : array
    {
        return $redactor->redact($data);
    }
}
