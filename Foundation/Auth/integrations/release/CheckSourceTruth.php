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
        $issues            = [];
        $checked           = [];
        $requiredDocuments = [
            'status'            => 'docs/STATUS.md',
            'product_boundary'  => 'docs/product-boundary.md',
            'capability_matrix' => 'docs/capability-matrix.md',
            'migration_guide'   => 'docs/upgrade-migration-guide.md',
        ];

        foreach ($requiredDocuments as $name => $relativePath) {
            $fullPath       = $repositoryRoot . '/' . $relativePath;
            $exists         = is_file(filename: $fullPath);
            $checked[$name] = $exists;

            if (! $exists) {
                $issues[] = "Missing canonical document: {$relativePath}";
            }
        }

        $status           = $this->read(path: $repositoryRoot . '/docs/STATUS.md');
        $riskRegister     = $this->read(path: $repositoryRoot . '/.agents/management/evidence/RISK_REGISTER.md');
        $authMerged       = $this->read(path: $repositoryRoot . '/Auth.txt');
        $capabilityMatrix = $this->read(path: $repositoryRoot . '/docs/capability-matrix.md');
        $currentState     = $this->read(path: $repositoryRoot . '/docs/current-state.md');

        if ($status !== '' && ! str_contains(haystack: $status, needle: 'This document is authoritative.')) {
            $issues[] = 'STATUS.md does not assert authoritative ownership.';
        }

        if (
            $riskRegister !== ''
            && str_contains(haystack: $riskRegister, needle: 'still does not ship OIDC provider behavior, client-credentials, SCIM runtime')
        ) {
            $issues[] = 'Risk register still contains stale platform-scope language for AUTH-RISK-006.';
        }

        if ($authMerged !== '' && ! str_contains(haystack: $authMerged, needle: 'non-canonical merged artifact')) {
            $issues[] = 'Auth.txt still reads like current-state truth instead of an archive/index.';
        }

        if (
            $capabilityMatrix !== ''
            && str_contains(haystack: $capabilityMatrix, needle: '| device trust assessment | ⚠️ partial | boundary |')
            && str_contains(haystack: $status, needle: 'Trusted device / remembered device support (permanently rejected')
        ) {
            $issues[] = 'Capability matrix still claims partial device trust while STATUS marks it as a non-goal.';
        }

        if ($currentState !== '' && str_contains(haystack: $currentState, needle: 'canonical current-state summary')) {
            $issues[] = 'docs/current-state.md still claims canonical status instead of deferring to STATUS.md.';
        }

        foreach ($this->legacySystemRootReferenceIssues(repositoryRoot: $repositoryRoot) as $issue) {
            $issues[] = $issue;
        }

        foreach ($this->capabilityMatrixEvidenceIssues(repositoryRoot: $repositoryRoot, capabilityMatrix: $capabilityMatrix) as $issue) {
            $issues[] = $issue;
        }

        foreach ($this->ownershipHowThisWorksIssues(repositoryRoot: $repositoryRoot) as $issue) {
            $issues[] = $issue;
        }

        return [
            'approved' => $issues === [],
            'issues'   => $issues,
            'checked'  => $checked,
        ];
    }

    private function read(string $path) : string
    {
        if (! is_file(filename: $path)) {
            return '';
        }

        $contents = file_get_contents(filename: $path);

        return is_string(value: $contents) ? $contents : '';
    }

    /**
     * @return list<string>
     */
    private function legacySystemRootReferenceIssues(string $repositoryRoot) : array
    {
        $issues        = [];
        $trackedFiles  = [
            'AGENTS.md',
            'docs/STATUS.md',
            'docs/architecture/system-shape.md',
            'docs/architecture/flow-boundaries.md',
            'docs/architecture/capability-boundaries.md',
        ];
        $legacyMarkers = [
            'System/Flow/',
            'System/Capability/',
        ];

        foreach ($trackedFiles as $relativePath) {
            $contents = $this->read(path: $repositoryRoot . '/' . $relativePath);

            if ($contents === '') {
                continue;
            }

            foreach ($legacyMarkers as $marker) {
                if (str_contains(haystack: $contents, needle: $marker)) {
                    $issues[] = "Legacy singular system root reference found in {$relativePath}: {$marker}";
                }
            }
        }

        return $issues;
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
        $lines  = preg_split(pattern: "/\r\n|\r|\n/", subject: $capabilityMatrix);

        if ($lines === false) {
            return ['Capability matrix could not be parsed.'];
        }

        foreach ($lines as $line) {
            if (! str_starts_with(haystack: $line, needle: '|')) {
                continue;
            }

            $columns = array_map(callback: trim(...), array: explode(separator: '|', string: trim(string: $line, characters: '|')));

            if (count(value: $columns) < 4) {
                continue;
            }

            if ($this->isCapabilityMatrixHeader(columns: $columns) || $this->isSeparatorRow(columns: $columns)) {
                continue;
            }

            [$capability, $status, , $evidenceColumn] = $columns;
            preg_match_all(pattern: '/`([^`]+)`/', subject: $evidenceColumn, matches: $matches);
            $paths = $matches[1];

            if ($paths === []) {
                $issues[] = "Capability matrix row '{$capability}' has no evidence paths.";
                continue;
            }

            foreach ($paths as $path) {
                if (str_starts_with(haystack: $path, needle: 'https://') || str_starts_with(haystack: $path, needle: 'http://')) {
                    continue;
                }

                $fullPath = rtrim(string: $repositoryRoot, characters: DIRECTORY_SEPARATOR) . '/' . $path;

                if (! is_file(filename: $fullPath) && ! is_dir(filename: $fullPath)) {
                    $issues[] = "Capability matrix evidence path missing for '{$capability}': {$path}";
                }
            }

            if (str_contains(haystack: $status, needle: 'supported') && ! $this->hasExecutableEvidence(paths: $paths)) {
                $issues[] = "Supported capability '{$capability}' is missing executable evidence in tests/ or build artifacts.";
            }
        }

        return $issues;
    }

    /**
     * @param list<string> $columns
     */
    private function isCapabilityMatrixHeader(array $columns) : bool
    {
        return isset($columns[0], $columns[1], $columns[2], $columns[3])
            && $columns[0] === 'Capability'
            && $columns[1] === 'Status'
            && $columns[2] === 'Ownership'
            && $columns[3] === 'Evidence';
    }

    /**
     * @param list<string> $columns
     */
    private function isSeparatorRow(array $columns) : bool
    {
        return array_all(array: $columns, callback: fn ($column) => ! ($column === '' || preg_match(pattern: '/^[-:]+$/', subject: $column) !== 1));
    }

    /**
     * @param list<string> $paths
     */
    private function hasExecutableEvidence(array $paths) : bool
    {
        return array_any(array: $paths, callback: fn ($path) => str_starts_with(haystack: $path, needle: 'tests/')
            || str_starts_with(haystack: $path, needle: 'build/')
            || str_contains(haystack: $path, needle: '/tests/'));
    }

    /**
     * @return list<string>
     */
    private function ownershipHowThisWorksIssues(string $repositoryRoot) : array
    {
        $issues        = [];
        $requiredPages = [
            'docs/System/how-this-works.md',
            'docs/System/Capabilities/how-this-works.md',
            'docs/System/Capabilities/Access/how-this-works.md',
            'docs/System/Capabilities/Diagnostics/how-this-works.md',
            'docs/System/Capabilities/ExternalIdentity/how-this-works.md',
            'docs/System/Capabilities/Identity/how-this-works.md',
            'docs/System/Capabilities/IdentitySync/how-this-works.md',
            'docs/System/Capabilities/Tenancy/how-this-works.md',
            'docs/System/Configuration/how-this-works.md',
            'docs/System/Flows/how-this-works.md',
            'docs/System/Foundation/how-this-works.md',
            'docs/integrations/how-this-works.md',
            'docs/integrations/avax-container/how-this-works.md',
            'docs/integrations/cookies/how-this-works.md',
            'docs/integrations/diagnostics/how-this-works.md',
            'docs/integrations/headers/how-this-works.md',
            'docs/integrations/http/how-this-works.md',
            'docs/integrations/release/how-this-works.md',
        ];

        foreach ($requiredPages as $relativePath) {
            $contents = $this->read(path: $repositoryRoot . '/' . $relativePath);

            if ($contents === '') {
                $issues[] = "Missing ownership how-this-works doc: {$relativePath}";
                continue;
            }

            foreach (['title:', 'owner:', 'last_reviewed:', 'classification:'] as $field) {
                if (! $this->hasFrontmatterField(contents: $contents, field: $field)) {
                    $issues[] = "Ownership doc is missing required frontmatter field '{$field}' in {$relativePath}";
                }
            }
        }

        return $issues;
    }

    private function hasFrontmatterField(string $contents, string $field) : bool
    {
        if (! str_starts_with(haystack: $contents, needle: "---\n") && ! str_starts_with(haystack: $contents, needle: "---\r\n")) {
            return false;
        }

        $delimiterPosition = strpos(haystack: $contents, needle: "\n---", offset: 4);

        if ($delimiterPosition === false) {
            return false;
        }

        $frontmatter = substr(string: $contents, offset: 0, length: $delimiterPosition);

        return str_contains(haystack: $frontmatter, needle: $field);
    }
}
