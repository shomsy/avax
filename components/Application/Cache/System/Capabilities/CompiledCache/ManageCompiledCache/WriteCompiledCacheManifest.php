<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\CompiledCache\ManageCompiledCache;

use Avax\Components\Application\Filesystem\System\PublicSurface\Filesystem;
use Random\RandomException;

final class WriteCompiledCacheManifest
{
    public function __construct(
        private Filesystem $filesystem,
    ) {
    }

    /**
     * @throws RandomException
     */
    public function write(CompiledCacheManifest $compiledCacheManifest, CompiledCachePath $compiledCachePath): void
    {
        $tempPath = $compiledCachePath->toString().'.tmp.'.bin2hex(random_bytes(8));

        $manifestDir = dirname($compiledCachePath->toString());
        if (! $this->filesystem->exists($manifestDir)) {
            $this->filesystem->createDirectory($manifestDir, 0o755);
        }

        $entries = [];
        foreach ($compiledCacheManifest->all() as $entry) {
            $entries[] = $entry->toArray();
        }

        $content = "<?php\n\ndeclare(strict_types=1);\n\nreturn ".var_export($entries, true).";\n";

        $written = $this->filesystem->write($tempPath, $content);

        if (! $written) {
            throw new CompiledCacheManifestWasInvalid('Failed to write manifest to temporary file');
        }

        if (! $this->filesystem->move($tempPath, $compiledCachePath->toString())) {
            $this->filesystem->delete($tempPath);

            throw new CompiledCacheManifestWasInvalid('Failed to rename manifest temporary file');
        }
    }
}
