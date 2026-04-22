<?php

// Include composer autoload to load DSL functions
require_once 'vendor/autoload.php';

// Test DSL autoload - functions should be available without require_once
echo "🧪 Testing DSL Autoload\n\n";

// Test Text DSL functions (from autoloaded Foundation/Text/functions.php)
echo "Text DSL functions:\n";
echo "rx_match function: " . (function_exists('rx_match') ? "✅ LOADED" : "❌ MISSING") . "\n";
echo "rx_replace function: " . (function_exists('rx_replace') ? "✅ LOADED" : "❌ MISSING") . "\n";
echo "text function: " . (function_exists('text') ? "✅ LOADED" : "❌ MISSING") . "\n";

// Test Router DSL functions (from autoloaded Foundation/HTTP/Router/functions.php)
echo "\nRouter DSL functions:\n";
echo "route_valid function: " . (function_exists('route_valid') ? "✅ LOADED" : "❌ MISSING") . "\n";
echo "route_params function: " . (function_exists('route_params') ? "✅ LOADED" : "❌ MISSING") . "\n";
echo "route_pattern function: " . (function_exists('route_pattern') ? "✅ LOADED" : "❌ MISSING") . "\n";
echo "route_group function: " . (function_exists('route_group') ? "✅ LOADED" : "❌ MISSING") . "\n";
echo "route_resource function: " . (function_exists('route_resource') ? "✅ LOADED" : "❌ MISSING") . "\n";
echo "route function: " . (function_exists('route') ? "✅ LOADED" : "❌ MISSING") . "\n";

// Test actual functionality if functions are loaded
if (function_exists('route_valid')) {
    echo "\nFunctional tests:\n";
    echo "route_valid('/users/{id}'): " . (route_valid(path: '/users/{id}') ? "✅ PASS" : "❌ FAIL") . "\n";
    echo "route_valid('users'): " . (route_valid(path: 'users') ? "❌ SHOULD FAIL" : "✅ PASS") . "\n";

    if (function_exists('route_params')) {
        $params = route_params(path: '/api/{version}/users/{userId}');
        echo "route_params(): [" . implode(', ', $params) . "]\n";
    }
}

echo "\n🎉 DSL Autoload Test Complete!\n";
echo "Composer autoload.files successfully configured for DSL helper functions.\n";