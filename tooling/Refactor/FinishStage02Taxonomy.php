<?php

declare(strict_types=1);

namespace Avax\Tooling\Refactor;

/**
 * Finishing Stage 02: Taxonomy Integrity Green.
 * Moves remaining non-canonical production roots and suite-level files.
 * 1:1 with avax-master-development-plan-v1.md.
 */
final class FinishStage02Taxonomy
{
    private string $basePath;

    public function __construct()
    {
        $this->basePath = dirname(__DIR__, 2);
    }

    public function execute(): void
    {
        echo "Finishing Stage 02 Taxonomy Repair...\n";

        // 1. Move framework/Foundation/Exception/ to framework/System/Foundation/Failure/
        $this->moveFile(
            'framework/Foundation/Exception/NotImplementedException.php',
            'framework/System/Foundation/Failure/NotImplemented.php',
            '<?php

declare(strict_types=1);

namespace Avax\Tooling\Refactor;

use Exception;

final class NotImplemented extends Exception
{
}
'
        );
        $this->removeDir('framework/Foundation');

        // 2. Address Suite-Level files in components/HTTP
        $this->moveFile(
            'components/HTTP/Configuration.php',
            'components/HTTP/System/Configuration/HttpConfiguration.php',
            null, // Just move and update namespace later
            'Avax\Components\HTTP',
            'Avax\Components\HTTP\System\Configuration'
        );

        $this->moveFile(
            'components/HTTP/RouterBootstrapper.php',
            'components/HTTP/Router/System/Configuration/RouterBootstrapper.php',
            null,
            'Avax\HTTP\Router',
            'Avax\Components\HTTP\Router\System\Configuration'
        );

        // 3. Move repo root clutter to examples or archive
        $this->createDir('examples/golden-path-app');
        $this->moveDir('routes', 'examples/golden-path-app/routes');
        $this->moveDir('storage', 'examples/golden-path-app/storage');
        $this->moveDir('public', 'examples/golden-path-app/public');

        $this->moveFile('merge-files.sh', 'tooling/refactor/merge-files.sh');
        $this->createDir('EVIDENCE/archive');
        $this->moveFile('avax.txt', 'EVIDENCE/archive/avax.txt');
        $this->removeFile('test-v2.php');

        // 4. Address components/ files
        $this->moveFile('components/new-component.md', 'docs/components/new-component.md');
        $this->moveFile('components/components.txt', 'EVIDENCE/archive/components.txt');

        echo "Stage 02 Taxonomy Repair Finished.\n";
    }

    private function moveFile(string $from, string $to, string|null $newContent = null, string|null $oldNs = null, string|null $newNs = null) : void
    {
        $fromPath = $this->basePath.'/'.$from;
        $toPath = $this->basePath.'/'.$to;

        if (! file_exists($fromPath)) {
            echo "Skipping: $from (not found)\n";

            return;
        }

        $toDir = dirname($toPath);
        if (! is_dir($toDir)) {
            mkdir($toDir, 0755, true);
        }

        if ($newContent !== null) {
            file_put_contents($toPath, $newContent);
        } else {
            $content = file_get_contents($fromPath);
            if ($oldNs !== null && $newNs !== null) {
                $content = str_replace("namespace $oldNs;", "namespace $newNs;", $content);
            }
            file_put_contents($toPath, $content);
        }

        unlink($fromPath);
        echo "Moved: $from -> $to\n";
    }

    private function removeDir(string $dir): void
    {
        $path = $this->basePath.'/'.$dir;
        if (is_dir($path)) {
            $this->delTree($path);
            echo "Removed: $dir/\n";
        }
    }

    private function delTree(string $dir): bool
    {
        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            (is_dir("$dir/$file")) ? $this->delTree("$dir/$file") : unlink("$dir/$file");
        }

        return rmdir($dir);
    }

    private function createDir(string $dir): void
    {
        $path = $this->basePath.'/'.$dir;
        if (! is_dir($path)) {
            mkdir($path, 0755, true);
            echo "Created: $dir/\n";
        }
    }

    private function moveDir(string $from, string $to): void
    {
        $fromPath = $this->basePath.'/'.$from;
        $toPath = $this->basePath.'/'.$to;

        if (! is_dir($fromPath)) {
            echo "Skipping: $from/ (not found)\n";

            return;
        }

        $toParent = dirname($toPath);
        if (! is_dir($toParent)) {
            mkdir($toParent, 0755, true);
        }

        rename($fromPath, $toPath);
        echo "Moved: $from/ -> $to/\n";
    }

    private function removeFile(string $file): void
    {
        $path = $this->basePath.'/'.$file;
        if (file_exists($path)) {
            unlink($path);
            echo "Removed: $file\n";
        }
    }
}

$finisher = new FinishStage02Taxonomy();
$finisher->execute();
