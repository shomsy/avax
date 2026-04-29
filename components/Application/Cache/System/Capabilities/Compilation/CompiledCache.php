<?php
declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\Compilation;

final class CompiledCache
{
    public function __construct(private string $path) {}

    public function compile(string $key, mixed $value): void
    {
        $content = '<?php return ' . var_export($value, true) . ';';
        file_put_contents($this->path . '/' . md5($key) . '.php', $content);
    }

    public function get(string $key): mixed
    {
        $file = $this->path . '/' . md5($key) . '.php';
        return file_exists($file) ? require $file : null;
    }
}
