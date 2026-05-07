<?php

declare(strict_types=1);

namespace Avax\Components\API\OpenAPI\System\Flows\CompareOpenApiDocuments;

use Avax\Components\API\OpenAPI\System\PublicSurface\OpenApiComparisonReport;
use Avax\Components\API\OpenAPI\System\PublicSurface\OpenApiDocument;

final readonly class CompareOpenApiDocuments
{
    public function compare(OpenApiDocument $oldDocument, OpenApiDocument $newDocument) : OpenApiComparisonReport
    {
        $oldOperations = $this->operations(document: $oldDocument);
        $newOperations = $this->operations(document: $newDocument);

        $removed = array_values(array_diff(array_keys($oldOperations), array_keys($newOperations)));
        $changed = [];

        foreach ($oldOperations as $operation => $signature) {
            if (isset($newOperations[$operation]) && $newOperations[$operation] !== $signature) {
                $changed[] = $operation;
            }
        }

        return new OpenApiComparisonReport(
            removedOperations: $removed,
            changedOperations: $changed,
        );
    }

    /**
     * @return array<string, string>
     */
    private function operations(OpenApiDocument $document) : array
    {
        $paths = $document->toArray()['paths'] ?? [];

        if (! is_array($paths)) {
            return [];
        }

        $operations = [];

        foreach ($paths as $path => $methods) {
            if (! is_string($path) || ! is_array($methods)) {
                continue;
            }

            foreach ($methods as $method => $operation) {
                if (! is_string($method) || ! is_array($operation)) {
                    continue;
                }

                $responses                                     = $operation['responses'] ?? [];
                $operations[strtoupper($method) . ' ' . $path] = json_encode($responses) ?: '';
            }
        }

        return $operations;
    }
}
