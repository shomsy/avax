<?php
$file    = 'components/Identity/Auth/System/Configuration/Builders/AuthBuilder.php';
$content = file_get_contents($file);

// Fix StartFederatedLogin parameter (exact spacing)
$content = str_replace('runtime        : $this->federationRuntime', 'federationRuntime: $this->federationRuntime', $content);

file_put_contents($file, $content);
echo "Fixed final parameters.\n";
