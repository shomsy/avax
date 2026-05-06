<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\CompiledCache\ManageCompiledCache;

use Random\RandomException;

final class WriteCompiledCacheManifest
{
    /**
     * @throws RandomException
     */
    public function write(CompiledCacheManifest $compiledCacheManifest, CompiledCachePath $compiledCachePath): void
    {
        $tempPath = $compiledCachePath->toString().'.tmp.'.bin2hex(random_bytes(8));

        $manifestDir = dirname($compiledCachePath->toString());
        if (! is_dir($manifestDir)) {
            mkdir($manifestDir, 0o755, true);
        }

        $entries = [];
        foreach ($compiledCacheManifest->all() as $entry) {
            $entries[] = $entry->toArray();
        }

        $content = "<?php\n\ndeclare(strict_types=1);\n\nreturn ".var_export($entries, true).";\n";

        $written = file_put_contents($tempPath, $content, LOCK_EX);

        if ($written === false) {
            throw new CompiledCacheManifestWasInvalid('Failed to write manifest to temporary file');
        }

        if (! rename($tempPath, $compiledCachePath->toString())) {
            @unlink($tempPath);

            throw new CompiledCacheManifestWasInvalid('Failed to rename manifest temporary file');
        }
    }
}
