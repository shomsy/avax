<?php

declare(strict_types=1);

namespace Avax\Components\API\Contracts\System\Capabilities\BreakingChangeDetector;

final class BreakingChangeDetector
{
    /**
     * @param array<string, mixed> $oldSchema
     * @param array<string, mixed> $newSchema
     *
     * @return list<array{type:string,path:string,message:string,severity:string}>
     */
    public function detect(array $oldSchema, array $newSchema) : array
    {
        $changes = [];

        $changes = array_merge(
            $changes,
            $this->detectRemovedEndpoints($oldSchema, $newSchema)
        );
        $changes = array_merge(
            $changes,
            $this->detectRemovedRequiredFields($oldSchema, $newSchema)
        );
        $changes = array_merge(
            $changes,
            $this->detectChangedResponseType($oldSchema, $newSchema)
        );

        return $changes;
    }

    /**
     * @param array<string, mixed> $oldSchema
     * @param array<string, mixed> $newSchema
     *
     * @return list<array{type:string,path:string,message:string,severity:string}>
     */
    private function detectRemovedEndpoints(array $oldSchema, array $newSchema) : array
    {
        $changes  = [];
        $oldPaths = $this->extractPaths($oldSchema);
        $newPaths = $this->extractPaths($newSchema);

        foreach ($oldPaths as $path) {
            if (! in_array(needle: $path, haystack: $newPaths, strict: true)) {
                $changes[] = [
                    'type'     => 'endpoint_removed',
                    'path'     => $path,
                    'message'  => "Endpoint {$path} was removed",
                    'severity' => 'breaking',
                ];
            }
        }

        return $changes;
    }

    /**
     * @param array<string, mixed> $schema
     *
     * @return list<string>
     */
    private function extractPaths(array $schema) : array
    {
        $paths = [];
        foreach ($schema['paths'] ?? [] as $path => $_) {
            $paths[] = $path;
        }

        return $paths;
    }

    /**
     * @param array<string, mixed> $oldSchema
     * @param array<string, mixed> $newSchema
     *
     * @return list<array{type:string,path:string,message:string,severity:string}>
     */
    private function detectRemovedRequiredFields(array $oldSchema, array $newSchema) : array
    {
        $changes     = [];
        $oldRequired = $this->extractRequiredFields($oldSchema);
        $newRequired = $this->extractRequiredFields($newSchema);

        foreach ($oldRequired as $path => $fields) {
            $newFields = $newRequired[$path] ?? [];
            foreach ($fields as $field) {
                if (! in_array(needle: $field, haystack: $newFields, strict: true)) {
                    $changes[] = [
                        'type'     => 'required_field_removed',
                        'path'     => "{$path}/{$field}",
                        'message'  => "Required field {$field} was removed from {$path}",
                        'severity' => 'breaking',
                    ];
                }
            }
        }

        return $changes;
    }

    /**
     * @param array<string, mixed> $schema
     *
     * @return array<string, list<string>>
     */
    private function extractRequiredFields(array $schema) : array
    {
        $required = [];
        foreach ($schema['paths'] ?? [] as $path => $methods) {
            foreach ($methods as $method => $definition) {
                $key            = "{$method}:{$path}";
                $required[$key] = $definition['required'] ?? [];
            }
        }

        return $required;
    }

    /**
     * @param array<string, mixed> $oldSchema
     * @param array<string, mixed> $newSchema
     *
     * @return list<array{type:string,path:string,message:string,severity:string}>
     */
    private function detectChangedResponseType(array $oldSchema, array $newSchema) : array
    {
        $changes  = [];
        $oldTypes = $this->extractResponseTypes($oldSchema);
        $newTypes = $this->extractResponseTypes($newSchema);

        foreach ($oldTypes as $path => $type) {
            if (isset($newTypes[$path]) && $newTypes[$path] !== $type) {
                $changes[] = [
                    'type'     => 'response_type_changed',
                    'path'     => $path,
                    'message'  => "Response type changed for {$path}",
                    'severity' => 'breaking',
                ];
            }
        }

        return $changes;
    }

    /**
     * @param array<string, mixed> $schema
     *
     * @return array<string, string>
     */
    private function extractResponseTypes(array $schema) : array
    {
        $types = [];
        foreach ($schema['paths'] ?? [] as $path => $methods) {
            foreach ($methods as $method => $definition) {
                $key         = "{$method}:{$path}";
                $types[$key] = $definition['response_type'] ?? 'unknown';
            }
        }

        return $types;
    }
}
