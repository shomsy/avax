<?php

declare(strict_types=1);

$base    = 'components/';
$mapping = [
    // 1. Application
    'Config'            => 'Application/Config',
    'Container'         => 'Application/Container',
    'Cache'             => 'Application/Cache',
    'Filesystem'        => 'Application/Filesystem',
    'Validation'        => 'Application/Validation',
    'Text'              => 'Application/Text',
    'DateTime'          => 'Application/DateTime',
    'Localization' => 'Application/Localization',
    'Facade'            => 'Application/Facade',
    'Translation'       => 'Application/Localization',
    'Lang'              => 'Application/Localization',
    'Carbon'            => 'Application/DateTime',
    'Date'              => 'Application/DateTime',
    'Time'              => 'Application/DateTime',
    'Storage'           => 'Application/Filesystem',

    // 2. HTTP
    'Request'           => 'HTTP/Request',
    'Response'          => 'HTTP/Response',
    'Router'            => 'HTTP/Router',
    'Middleware'        => 'HTTP/Middleware',
    'Middlewares'       => 'HTTP/Middleware',
    'Session'           => 'HTTP/Session',
    'Cookies'           => 'HTTP/Cookies',
    'Cookie'            => 'HTTP/Cookies',
    'URI'               => 'HTTP/URI',
    'Url'               => 'HTTP/URI',
    'Uploads'           => 'HTTP/Uploads',
    'UploadedFiles'     => 'HTTP/Uploads',
    'HttpContext'       => 'HTTP/Context',
    'Context'           => 'HTTP/Context',
    'HttpClient'        => 'HTTP/Client',
    'Csrf'              => 'HTTP/Security',
    'SignedUrls'        => 'HTTP/Security',
    'TrustedProxy'      => 'HTTP/Security',
    'TrustedHost'       => 'HTTP/Security',
    'SecurityHeaders' => 'HTTP/Security',

    // 3. CLI
    'Console'           => 'CLI/Console',
    'Commands' => 'CLI/Console',
    'Command'           => 'CLI/Console',

    // 4. DataStack
    'Data'              => 'DataStack/Data',
    'DataFoundation' => 'DataStack/Data',
    'Collections'       => 'DataStack/Data',
    'Collection'        => 'DataStack/Data',
    'Arr'               => 'DataStack/Data',
    'Arrhae'            => 'DataStack/Data',
    'DTO'               => 'DataStack/Data',
    'DataTransfer'      => 'DataStack/Data',
    'ObjectMapping'     => 'DataStack/Data',
    'Values'            => 'DataStack/Data',
    'Structures'        => 'DataStack/Data',
    'Database'          => 'DataStack/Database',
    'Schema'            => 'DataStack/Database',
    'Migrations'        => 'DataStack/Database',
    'QueryBuilder'      => 'DataStack/Database',
    'Transactions'      => 'DataStack/Database',
    'Persistence'       => 'DataStack/Persistence',
    'DataLayer'         => 'DataStack/Persistence',
    'ORM'               => 'DataStack/Persistence',
    'Repository'        => 'DataStack/Persistence',
    'EntityManager'     => 'DataStack/Persistence',
    'UnitOfWork'        => 'DataStack/Persistence',
    'IdentityMap'       => 'DataStack/Persistence',

    // 5. Identity
    'Auth'              => 'Identity/Auth',
    'Authentication'    => 'Identity/Auth',
    'Authorization'     => 'Identity/Access',
    'Access'            => 'Identity/Access',
    'Permissions'       => 'Identity/Access',
    'Roles'             => 'Identity/Access',
    'Policies'          => 'Identity/Access',
    'Gates'             => 'Identity/Access',
    'Passwords'         => 'Identity/Credentials',
    'Password'          => 'Identity/Credentials',
    'MFA'               => 'Identity/Credentials',
    'Mfa'               => 'Identity/Credentials',
    'Passkey'           => 'Identity/Credentials',
    'Passkeys'          => 'Identity/Credentials',
    'RecoveryCodes'     => 'Identity/Credentials',
    'Tokens'            => 'Identity/Tokens',
    'JWT'               => 'Identity/Tokens',
    'Jwt'               => 'Identity/Tokens',
    'ApiTokens'         => 'Identity/Tokens',
    'AccessTokens'      => 'Identity/Tokens',
    'RefreshTokens'     => 'Identity/Tokens',
    'OAuth'             => 'Identity/ExternalIdentity',
    'Oidc'              => 'Identity/ExternalIdentity',
    'OpenIDConnect'     => 'Identity/ExternalIdentity',
    'SocialLogin'       => 'Identity/ExternalIdentity',
    'ExternalIdentity' => 'Identity/ExternalIdentity',
    'Federation'        => 'Identity/ExternalIdentity',
    'SSO'               => 'Identity/ExternalIdentity',
    'SingleSignOn'      => 'Identity/ExternalIdentity',
    'Tenancy'           => 'Identity/Tenancy',
    'Tenants'           => 'Identity/Tenancy',
    'Membership'        => 'Identity/Tenancy',
    'Invitations'       => 'Identity/Tenancy',

    // 6. Security
    'Encryption'        => 'Security/Cryptography',
    'Cryptography'      => 'Security/Cryptography',
    'Encryptor'         => 'Security/Cryptography',
    'Signer'            => 'Security/Cryptography',
    'Signing'           => 'Security/Cryptography',
    'Keys'              => 'Security/Cryptography',
    'KeyRotation'       => 'Security/Cryptography',
    'Hashing'           => 'Security/Hashing',
    'Hasher'            => 'Security/Hashing',
    'PasswordHashing' => 'Security/Hashing',
    'Secrets'           => 'Security/Secrets',
    'Secret'            => 'Security/Secrets',
    'Vault'             => 'Security/Secrets',
    'Redaction'         => 'Security/Redaction',
    'SecretRedaction' => 'Security/Redaction',
    'SensitiveData'     => 'Security/Redaction',
    'Random'            => 'Security/Random',
    'SecureRandom'      => 'Security/Random',
    'SecurityAudit'     => 'Security/Audit',
    'Audit'             => 'Security/Audit',

    // 7. Operations
    'Events'            => 'Operations/Events',
    'Event'             => 'Operations/Events',
    'EventDispatcher'   => 'Operations/Events',
    'Listeners'         => 'Operations/Events',
    'Logging'           => 'Operations/Logging',
    'Logger'            => 'Operations/Logging',
    'Logs'              => 'Operations/Logging',
    'Mail'              => 'Operations/Mail',
    'Mailer'            => 'Operations/Mail',
    'Queue'             => 'Operations/Queue',
    'Queues'            => 'Operations/Queue',
    'Jobs'              => 'Operations/Queue',
    'Bus'               => 'Operations/Queue',
    'Tasks'             => 'Operations/Queue',
    'Notifications'     => 'Operations/Notifications',
    'Notification'      => 'Operations/Notifications',
    'Scheduler'         => 'Operations/Scheduler',
    'Schedule'          => 'Operations/Scheduler',
    'Cron'              => 'Operations/Scheduler',
    'Resilience'        => 'Operations/Resilience',
    'Retry'             => 'Operations/Resilience',
    'Timeout'           => 'Operations/Resilience',
    'CircuitBreaker'    => 'Operations/Resilience',
    'RateLimiter'       => 'Operations/Resilience',
    'Observability'     => 'Operations/Observability',
    'Telemetry'         => 'Operations/Observability',
    'Tracing'           => 'Operations/Observability',
    'Metrics'           => 'Operations/Observability',
    'Timeline'          => 'Operations/Observability',
    'ApplicationWorkflow' => 'Operations/ApplicationWorkflow',
    'Workflow'          => 'Operations/ApplicationWorkflow',
    'Saga'              => 'Operations/ApplicationWorkflow',
    'Sagas'             => 'Operations/ApplicationWorkflow',

    // 8. Presentation
    'View'              => 'Presentation/View',
    'Views'             => 'Presentation/View',
    'Template'          => 'Presentation/View',
    'Templates' => 'Presentation/View',
    'Blade'             => 'Presentation/View',
    'BladeOne'          => 'Presentation/View',
    'Renderer'          => 'Presentation/View',

    // 9. DeveloperTools
    'Diagnostics'       => 'DeveloperTools/Diagnostics',
    'Doctor'            => 'DeveloperTools/Diagnostics',
    'HealthCheck'       => 'DeveloperTools/Diagnostics',
    'DumpDebugger'      => 'DeveloperTools/DumpDebugger',
    'Dumper'            => 'DeveloperTools/DumpDebugger',
    'Debug'             => 'DeveloperTools/DumpDebugger',
    'Whoops'            => 'DeveloperTools/DumpDebugger',
    'Ignition'          => 'DeveloperTools/DumpDebugger',
    'ArchitectureReview' => 'DeveloperTools/ArchitectureReview',
    'ArchitectureCheck' => 'DeveloperTools/ArchitectureReview',
    'CodeReview'        => 'DeveloperTools/ArchitectureReview',
    'Testing'           => 'DeveloperTools/Testing',
    'Fakes'             => 'DeveloperTools/Testing',
    'TestDoubles'       => 'DeveloperTools/Testing',
    'CodeGeneration'    => 'DeveloperTools/CodeGeneration',
    'Generators'        => 'DeveloperTools/CodeGeneration',
    'Scaffolding'       => 'DeveloperTools/CodeGeneration',
    'Make'              => 'DeveloperTools/CodeGeneration',
    'Profiler'          => 'DeveloperTools/Profiler',
    'PerformanceProfiler' => 'DeveloperTools/Profiler',
];

// Phase 1: Move top-level components to suites
foreach ($mapping as $source => $target) {
    $sourcePath = $base . $source;
    $targetPath = $base . $target;

    if (is_dir($sourcePath)) {
        if ($sourcePath === $targetPath) {
            continue;
        }

        if (! is_dir(dirname($targetPath))) {
            mkdir(dirname($targetPath), 0o777, true);
        }

        // If target exists, move contents inside
        if (is_dir($targetPath)) {
            echo "Merging $sourcePath into $targetPath\n";
            $files = array_diff(scandir($sourcePath), ['.', '..']);
            foreach ($files as $file) {
                rename($sourcePath . '/' . $file, $targetPath . '/' . $file);
            }
            rmdir($sourcePath);
        } else {
            echo "Moving $sourcePath to $targetPath\n";
            rename($sourcePath, $targetPath);
        }
    }
}

// Phase 2: Cleanup and special rules (Generators split)
$genSource = $base . 'CLI/Console/System/Capabilities/Generators';
$genTarget = $base . 'DeveloperTools/CodeGeneration/System/Capabilities/Generators';

if (is_dir($genSource)) {
    if (! is_dir(dirname($genTarget))) {
        mkdir(dirname($genTarget), 0o777, true);
    }
    echo "Splitting Generators to DeveloperTools...\n";
    rename($genSource, $genTarget);
}

echo "\n✨ MOVEMENT COMPLETE! Components are now properly suited up.\n";
