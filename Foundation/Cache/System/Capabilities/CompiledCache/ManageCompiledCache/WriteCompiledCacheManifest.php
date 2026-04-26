<?php

declare(strict_types=1);

namespace Avax\Cache\System\Capabilities\CompiledCache\ManageCompiledCache;

use Avax\Cache\System\Foundation\Time\Clock;
use Avax\Cache\System\Foundation\Time\SystemClock;
use Random\RandomException;

final class WriteCompiledCacheManifest
{
    public function __construct(
        private Clock $clock = new SystemClock()
    ) {}

    /**
     * @throws RandomException
     */
    public function write(CompiledCacheManifest $manifest, CompiledCachePath $manifestPath) : void
    {
        $tempPath = $manifestPath->toString() . '.tmp.' . bin2hex(random_bytes(8));

        $manifestDir = dirname($manifestPath->toString());
        if (! is_dir($manifestDir)) {
            mkdir($manifestDir, 0755, true);
        }

        $entries = [];
        foreach ($manifest->all() as $entry) {
            $entries[] = $entry->toArray();
        }

        $content = "<?php\n\ndeclare(strict_types=1);\n\nreturn " . var_export($entries, true) . ";\n";

        $written = file_put_contents($tempPath, $content, LOCK_EX);

        if ($written === false) {
            throw new CompiledCacheManifestWasInvalid('Failed to write manifest to temporary file');
        }

        if (! rename($tempPath, $manifestPath->toString())) {
            @unlink($tempPath);
            throw new CompiledCacheManifestWasInvalid('Failed to rename manifest temporary file');
        }
    }
}