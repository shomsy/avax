<?php

declare(strict_types=1);

namespace Avax\Tooling\Refactor;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

final class CheckDocsMirror
{
    private array $forbiddenDocRefs = [
        'Foundation/HTTP',
        'Foundation/DataLayer',
        'Foundation/DataHandling',
    ];

    private array $errors = [];

    public function check() : array
    {
        $this->scanDocsForObsoleteRefs();

        return [
            'status' => empty($this->errors) ? 'PASS' : 'FAIL',
            'errors' => $this->errors,
        ];
    }

    private function scanDocsForObsoleteRefs() : void
    {
        $docsPath = dirname(__DIR__, 2) . '/docs';

        if (! is_dir($docsPath)) {
            return;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($docsPath),
            RecursiveIteratorIterator::SELF_FIRST,
        );

        /** @var SplFileInfo $file */
        foreach ($iterator as $file) {
            if ($file->getExtension() !== 'md' && $file->getExtension() !== 'php') {
                continue;
            }

            $content = file_get_contents($file->getPathname());
            foreach ($this->forbiddenDocRefs as $ref) {
                if (str_contains($content, $ref)) {
                    $this->errors[] = $file->getPathname() . ': references obsolete ' . $ref;
                }
            }
        }
    }
}

if (PHP_SAPI === 'cli' && basename(__FILE__) === basename($argv[0] ?? '')) {
    $checker = new CheckDocsMirror();
    $result = $checker->check();

    echo $result['status'] . "\n";

    if (! empty($result['errors'])) {
        echo implode("\n", $result['errors']) . "\n";
        exit(1);
    }

    exit(0);
}
