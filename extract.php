<?php
$sourceFile = __DIR__ . '/Foundation/Framework.txt';
$targetDir  = __DIR__ . '/Foundation/ValidationBackup';

if (! file_exists($targetDir)) {
    mkdir($targetDir, 0777, true);
}

$lines       = file($sourceFile);
$currentFile = null;
$fileContent = [];

foreach ($lines as $line) {
    if (preg_match('/^=== DataHandling\/Validation\/(.+) ===$/', trim($line), $matches)) {
        if ($currentFile) {
            $dir = dirname($targetDir . '/' . $currentFile);
            if (! file_exists($dir)) {
                mkdir($dir, 0777, true);
            }
            file_put_contents($targetDir . '/' . $currentFile, implode("", $fileContent));
        }
        $currentFile = $matches[1];
        $fileContent = [];
        echo "Extracting: $currentFile\n";
    } elseif ($currentFile !== null && preg_match('/^=== .+ ===$/', trim($line))) {
        if ($currentFile) {
            $dir = dirname($targetDir . '/' . $currentFile);
            if (! file_exists($dir)) {
                mkdir($dir, 0777, true);
            }
            file_put_contents($targetDir . '/' . $currentFile, implode("", $fileContent));
        }
        $currentFile = null;
    } elseif ($currentFile !== null) {
        $fileContent[] = $line;
    }
}
if ($currentFile) {
    $dir = dirname($targetDir . '/' . $currentFile);
    if (! file_exists($dir)) {
        mkdir($dir, 0777, true);
    }
    file_put_contents($targetDir . '/' . $currentFile, implode("", $fileContent));
}
echo "Done.\n";
