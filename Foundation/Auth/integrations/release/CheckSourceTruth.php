<?php

declare(strict_types=1);

namespace Avax\Auth\Integrations\Release;

final readonly class CheckSourceTruth
{
    /**
     * @return array{
     *     approved:bool,
     *     issues:list<string>,
     *     checked:array<string, bool>
     * }
     */
    public function execute(string $repositoryRoot) : array
    {
        $issues = [];
        $checked = [];
        $requiredDocuments = [
            'status' => 'docs/STATUS.md',
            'product_boundary' => 'docs/product-boundary.md',
            'capability_matrix' => 'docs/capability-matrix.md',
            'migration_guide' => 'docs/upgrade-migration-guide.md',
        ];

        foreach ($requiredDocuments as $name => $relativePath) {
            $fullPath = $repositoryRoot . '/' . $relativePath;
            $exists = is_file($fullPath);
            $checked[$name] = $exists;

            if (! $exists) {
                $issues[] = "Missing canonical document: {$relativePath}";
            }
        }

        $status = $this->read($repositoryRoot . '/docs/STATUS.md');
        $riskRegister = $this->read($repositoryRoot . '/.agents/management/evidence/RISK_REGISTER.md');
        $authMerged = $this->read($repositoryRoot . '/Auth.txt');
        $capabilityMatrix = $this->read($repositoryRoot . '/docs/capability-matrix.md');
        $currentState = $this->read($repositoryRoot . '/docs/current-state.md');

        if ($status !== '' && ! str_contains($status, 'This document is authoritative.')) {
            $issues[] = 'STATUS.md does not assert authoritative ownership.';
        }

        if (
            $riskRegister !== ''
            && str_contains($riskRegister, 'still does not ship OIDC provider behavior, client-credentials, SCIM runtime')
        ) {
            $issues[] = 'Risk register still contains stale platform-scope language for AUTH-RISK-006.';
        }

        if ($authMerged !== '' && ! str_contains($authMerged, 'non-canonical merged artifact')) {
            $issues[] = 'Auth.txt still reads like current-state truth instead of an archive/index.';
        }

        if (
            $capabilityMatrix !== ''
            && str_contains($capabilityMatrix, '| device trust assessment | ⚠️ partial | boundary |')
            && str_contains($status, 'Trusted device / remembered device support (permanently rejected')
        ) {
            $issues[] = 'Capability matrix still claims partial device trust while STATUS marks it as a non-goal.';
        }

        if ($currentState !== '' && str_contains($currentState, 'canonical current-state summary')) {
            $issues[] = 'docs/current-state.md still claims canonical status instead of deferring to STATUS.md.';
        }

        foreach ($this->capabilityMatrixEvidenceIssues(repositoryRoot: $repositoryRoot, capabilityMatrix: $capabilityMatrix) as $issue) {
            $issues[] = $issue;
        }

        return [
            'approved' => $issues === [],
            'issues' => $issues,
            'checked' => $checked,
        ];
    }

    /**
     * @return list<string>
     */
    private function capabilityMatrixEvidenceIssues(string $repositoryRoot, string $capabilityMatrix) : array
    {
        if ($capabilityMatrix === '') {
            return [];
        }

        $issues = [];
        $lines = preg_split("/\r\n|\r|\n/", $capabilityMatrix);

        if ($lines === false) {
            return ['Capability matrix could not be parsed.'];
        }

        foreach ($lines as $line) {
            if (! str_starts_with($line, '|')) {
                continue;
            }

            if (
                str_contains($line, '| Capability |')
                || str_contains($line, '|---|')
                || str_contains($line, '| Symbol |')
            ) {
                continue;
            }

            $columns = array_map('trim', explode('|', trim($line, '|')));

            if (count($columns) < 4) {
                continue;
            }

            [$capability, $status, , $evidenceColumn] = $columns;
            preg_match_all('/`([^`]+)`/', $evidenceColumn, $matches);
            $paths = $matches[1];

            if ($paths === []) {
                $issues[] = "Capability matrix row '{$capability}' has no evidence paths.";
                continue;
            }

            foreach ($paths as $path) {
                if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
                    continue;
                }

                $fullPath = rtrim($repositoryRoot, DIRECTORY_SEPARATOR) . '/' . $path;

                if (! is_file($fullPath) && ! is_dir($fullPath)) {
                    $issues[] = "Capability matrix evidence path missing for '{$capability}': {$path}";
                }
            }

            if (str_contains($status, 'supported') && ! $this->hasExecutableEvidence(paths: $paths)) {
                $issues[] = "Supported capability '{$capability}' is missing executable evidence in tests/ or build artifacts.";
            }
        }

        return $issues;
    }

    /**
     * @param list<string> $paths
     */
    private function hasExecutableEvidence(array $paths) : bool
    {
        foreach ($paths as $path) {
            if (
                str_starts_with($path, 'tests/')
                || str_starts_with($path, 'build/')
                || str_contains($path, '/tests/')
            ) {
                return true;
            }
        }

        return false;
    }

    private function read(string $path) : string
    {
        if (! is_file($path)) {
            return '';
        }

        $contents = file_get_contents($path);

        return is_string($contents) ? $contents : '';
    }
}
