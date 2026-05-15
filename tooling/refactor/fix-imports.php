<?php
$authBuilderFile = 'components/Identity/Auth/System/Configuration/Builders/AuthBuilder.php';
$content = file_get_contents($authBuilderFile);

// Fix SyncFederationMetadata parameter (revert to federationMetadataRuntime)
$content = str_replace('federationHealthCheck: $this->federationRuntime,', 'federationMetadataRuntime: $this->federationRuntime,', $content);

file_put_contents($authBuilderFile, $content);
echo "Fixed SyncFederationMetadata parameter.\n";
