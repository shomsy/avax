<?php


namespace Avax\Tooling\Refactor;

use Avax\Components\HTTP\ContentNegotiation\System\Capabilities\Formats\ContentFormatterInterface;
use Avax\Components\HTTP\ContentNegotiation\System\Capabilities\Formats\CsvFormat;
use Avax\Components\HTTP\ContentNegotiation\System\Capabilities\Formats\JsonFormat;
use Avax\Components\HTTP\ContentNegotiation\System\Capabilities\Formats\XmlFormat;
use Avax\Components\Operations\MessageBus\System\Capabilities\Bus\CommandBus;
use Avax\Components\Operations\MessageBus\System\Capabilities\Bus\EventBus;
use Avax\Components\Operations\MessageBus\System\Capabilities\Bus\QueryBus;
use Avax\Components\Operations\Queue\System\Capabilities\TaskDispatch\Dispatchers\AsyncDispatcher;
use Avax\Components\Operations\Queue\System\Capabilities\TaskDispatch\Dispatchers\SyncDispatcher;

$basePath = dirname(__DIR__, 2);
$files = [
    'components/Operations/MessageBus/System/Capabilities/Bus/MessageBusTraits.php' => [
        CommandBus::class => 'CommandBus',
        QueryBus::class => 'QueryBus',
        EventBus::class => 'EventBus',
    ],
    'components/Operations/Queue/System/Capabilities/TaskDispatch/Dispatchers/TaskDispatchers.php' => [
        SyncDispatcher::class => 'SyncDispatcher',
        AsyncDispatcher::class => 'AsyncDispatcher',
        'Avax\Components\Operations\Queue\System\Capabilities\TaskDispatch\Dispatchers\DeferredDispatcher' => 'DeferredDispatcher',
    ],
    'components/HTTP/ContentNegotiation/System/Capabilities/Formats/ContentFormatters.php' => [
        ContentFormatterInterface::class => 'ContentFormatterInterface',
        JsonFormat::class => 'JsonFormat',
        XmlFormat::class => 'XmlFormat',
        CsvFormat::class => 'CsvFormat',
    ],
];

foreach ($files as $file => $classes) {
    $path = $basePath . '/' . $file;
    if (!file_exists($path)) {
        echo sprintf('NOT FOUND: %s%s', $file, PHP_EOL);
        continue;
    }

    $content = file_get_contents($path);
    preg_match('/^namespace\s+([A-Za-z\\\\]+);/m', $content, $nsMatch);
    $namespace = $nsMatch[1] ?? '';

    $dir = dirname($path);
    foreach ($classes as $classFull => $className) {
        if (preg_match('/^.*\\\\(' . preg_quote($className) . ')$/', $classFull, $m)) {
            $classPath = $dir . '/' . $className . '.php';
            if (!file_exists($classPath)) {
                preg_match('/^(final\s+)?(class|interface)\s+' . preg_quote($className) . '/m', $content, $match, PREG_OFFSET_CAPTURE);
                if ($match !== []) {
                    $start = $match[0][1];
                    $end = strlen($content);
                    $sub = substr($content, $start);
                    $braceCount = 0;
                    $inClass = false;
                    for ($i = 0; $i < strlen($sub); $i++) {
                        if ($sub[$i] === '{') {
                            $braceCount++;
                            $inClass = true;
                        }

                        if ($sub[$i] === '}') {
                            $braceCount--;
                        }

                        if ($inClass && $braceCount === 0) {
                            $end = $start + $i + 1;
                            break;
                        }
                    }

                    $classCode = substr($content, $start, $end - $start);

                    $newContent = "<?php\n\ndeclare(strict_types=1);\n\nnamespace {$namespace};\n\n" . trim($classCode) . "\n";
                    file_put_contents($classPath, $newContent);
                    echo sprintf('CREATED: %s%s', $classPath, PHP_EOL);
                }
            }
        }
    }

    unlink($path);
    echo sprintf('REMOVED: %s%s', $file, PHP_EOL);
}

echo "\nDone!\n";
