<?php

declare(strict_types=1);

namespace Avax\Auth\Integrations\Release;

final readonly class GenerateEvidenceBundle
{
    /**
     * @param array<string, string> $artifactPaths
     *
     * @return array{
     *     generated_at:string,
     *     version:string,
     *     package:string,
     *     repository_root:string,
     *     artifacts:array<string, array{path:string, exists:bool, size?:int, modified_at?:string}>,
     *     canonical_docs:array<string, string>,
     *     missing_artifacts:list<string>
     * }
     */
    public function execute(string $repositoryRoot, array $artifactPaths = []) : array
    {
        $resolvedPath = realpath(path: $repositoryRoot);
        $resolvedRoot = $resolvedPath !== false ? $resolvedPath : $repositoryRoot;
        $artifacts    = [];
        $missing      = [];

        foreach ($artifactPaths !== [] ? $artifactPaths : $this->defaultArtifacts() as $type => $relativePath) {
            $fullPath = $resolvedRoot . '/' . $relativePath;

            if (! is_file(filename: $fullPath)) {
                $artifacts[$type] = [
                    'path'   => $relativePath,
                    'exists' => false,
                ];
                $missing[]        = $type;
                continue;
            }

            $size       = filesize(filename: $fullPath);
            $modifiedAt = filemtime(filename: $fullPath);

            $artifacts[$type] = [
                'path'        => $relativePath,
                'exists'      => true,
                'size'        => $size === false ? 0 : $size,
                'modified_at' => $modifiedAt === false ? '' : gmdate(format: DATE_ATOM, timestamp: $modifiedAt),
            ];
        }

        return [
            'generated_at'      => gmdate(format: DATE_ATOM),
            'version'           => '1.0.0',
            'package'           => $this->detectPackageName(repositoryRoot: $resolvedRoot),
            'repository_root'   => $resolvedRoot,
            'artifacts'         => $artifacts,
            'canonical_docs'    => [
                'status'              => 'docs/STATUS.md',
                'product_boundary'    => 'docs/product-boundary.md',
                'capability_matrix'   => 'docs/capability-matrix.md',
                'migration_guide'     => 'docs/upgrade-migration-guide.md',
                'deployment_profiles' => 'docs/supported-deployment-profiles.md',
            ],
            'missing_artifacts' => $missing,
        ];
    }

    /**
     * @return array<string, string>
     */
    private function defaultArtifacts() : array
    {
        return [
            'conformance'   => 'build/conformance-report.json',
            'quality_gates' => 'build/quality-gates-report.json',
            'mutation'      => 'build/infection-summary.log',
            'sbom'          => 'build/sbom.json',
            'rollback'      => 'build/rollback-evidence.json',
            'provenance'    => 'build/release-provenance.json',
        ];
    }

    private function detectPackageName(string $repositoryRoot) : string
    {
        $composerJsonPath = rtrim(string: $repositoryRoot, characters: DIRECTORY_SEPARATOR) . '/composer.json';

        if (! is_file(filename: $composerJsonPath)) {
            return 'unknown';
        }

        $json    = file_get_contents(filename: $composerJsonPath);
        $decoded = is_string(value: $json) ? json_decode(json: $json, associative: true) : null;

        return is_array(value: $decoded) && is_string(value: $decoded['name'] ?? null)
            ? $decoded['name']
            : 'unknown';
    }
}
