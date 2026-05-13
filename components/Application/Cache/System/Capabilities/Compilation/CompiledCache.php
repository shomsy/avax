<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\Compilation;

use Avax\Components\Application\Filesystem\System\PublicSurface\Filesystem;

final class CompiledCache
{
    public function __construct(
        private string $path,
        private Filesystem $filesystem,
    )
    {
    }

    public function compile(string $key, mixed $value): void
    {
        $content = '<?php return '.var_export($value, true).';';
        $this->filesystem->write($this->path . '/' . md5($key) . '.php', $content);
    }

    public function get(string $key): mixed
    {
        $file = $this->path.'/'.md5($key).'.php';

        return $this->filesystem->exists($file) ? require $file : null;
    }
}
