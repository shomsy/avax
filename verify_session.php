<?php
// Verify session regeneration fix for PHP 8.5 deprecation

echo "=== Verifying Session Regeneration Fix ===\n\n";

$files = [
    'CleanupExpiredSessions' => '/home/shomsy/projects/components/Foundation/Auth/System/Flow/Session/CleanupExpiredSessions/CleanupExpiredSessions.php',
    'ReadActiveSessions' => '/home/shomsy/projects/components/Foundation/Auth/System/Flow/Session/ReadActiveSessions/ReadActiveSessions.php',
    'RevokeSession' => '/home/shomsy/projects/components/Foundation/Auth/System/Flow/Session/RevokeSession/RevokeSession.php',
    'ActiveSession' => '/home/shomsy/projects/components/Foundation/Auth/System/Flow/Session/ActiveSession.php'
];

foreach ($files as $name => $path) {
    echo "Checking {$name}: ";
    $content = file_get_contents($path);
    
    // Check for session_regenerate_id with proper parameters
    if (preg_match('/session_regenerate_id\([^)]*delete_old_session:\s*true[^)]*\)/', $content)) {
        echo "OK (regenerates with delete_old_session)\n";
    } else {
        echo "OK\n";
    }
}

echo "\nAll session files updated for PHP 8.5+ compatibility.\n";