<?php

require_once 'Foundation/Text/Text.php';
require_once 'Foundation/Text/Pattern.php';
require_once 'Foundation/Text/MatchResult.php';
require_once 'Foundation/Text/RegexException.php';

echo "🧪 Testing Text DSL Implementation\n\n";

// Test basic functionality
$text = Avax\Text\Text::of(value: 'test@example.com');
echo "Email validation: " . ($text->isValidEmail() ? "✅ PASS" : "❌ FAIL") . "\n";

$text2 = Avax\Text\Text::of(value: '123abc');
echo "Digits only: " . ($text2->containsOnlyDigits() ? "✅ PASS" : "❌ FAIL") . "\n";
echo "Alphanumeric: " . ($text2->containsOnlyAlphanumeric() ? "✅ PASS" : "❌ FAIL") . "\n";

$text3 = Avax\Text\Text::of(value: 'Hello World 123');
echo "Extract digits: '" . $text3->extractDigits() . "'\n";
echo "Extract letters: '" . $text3->extractLetters() . "'\n";
echo "Word count: " . $text3->countWords() . "\n";

$text4 = Avax\Text\Text::of(value: '/users/{id}/posts/{slug}');
echo "Route path validation: " . ($text4->validateRoutePath() ? "✅ PASS" : "❌ FAIL") . "\n";
echo "Route parameters: " . json_encode($text4->extractRouteParameters()) . "\n";

$text5 = Avax\Text\Text::of(value: 'user_name');
echo "Valid route param: " . ($text5->matchesRouteParameter('user_name') ? "✅ PASS" : "❌ FAIL") . "\n";

echo "\n🎉 DSL Test Complete!\n";