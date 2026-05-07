<?php

declare(strict_types=1);

namespace Avax\Components\Security\Privacy\System\Flows\ExportUserData;

use Avax\Components\Security\Privacy\System\Capabilities\DataExporter\DataExporter;

final readonly class ExportUserData
{
    public function __construct(
        private DataExporter $exporter = new DataExporter(),
    ) {}

    /**
     * @param array<string, mixed> $userData
     */
    public function execute(array $userData, string $format = 'json') : string
    {
        return $this->exporter->export(data: $userData, format: $format);
    }
}
