# Text & Regex DSL - Human-Grade String Processing

Functional, immutable DSL for string and regex operations in PHP 8+. PSR-12 compliant with comprehensive error handling.

## Design Constraints

* **Immutable chain** - All operations return new instances, no side effects
* **No global state** - Pure functions, dependency injection ready
* **Throw on PCRE errors** - RegexException with detailed error context
* **Pattern handles delimiter/flags centrally** - Call sites use raw patterns (without delimiters)
* **Router refactor** - Replace regex only where there's real benefit (validation/compile/URL gen), not everywhere
* **Unicode by default** - All text operations support UTF-8, regex patterns default to 'u' flag for Unicode

## Core Classes

### Text - Immutable String DSL

```php
use Avax\Text\Text;

$text = Text::of('  Šta ima, Miloš?   ')
    ->collapseWhitespace()
    ->toAscii()
    ->slug()
    ->toString();
// Result: "sta-ima-milos"
```

### Pattern - Regex DSL

```php
use Avax\Text\Pattern;

$pattern = Pattern::of('^(?<user>[^@]+)@(?<host>.+)$');
$result = $pattern->match('john@site.com');
$user = $result->group('user'); // "john"
```

### MatchResult - Typed Match Results

```php
$result = Pattern::of('pattern')->match('subject');
$named = $result->namedGroups();     // ['key' => 'value']
$group = $result->group('key');      // 'value' or null
$full = $result->fullMatch();        // Full match or null
```

### RegexException - Proper Error Handling

```php
try {
    Pattern::of('invalid[pattern')->test('subject');
} catch (RegexException $e) {
    // Detailed PCRE error with context
    echo $e->getMessage();
}
```

## Functional Helpers

```php
use function Avax\Text\{text, rx_match, slug, before};

$slug = slug('Šta ima, Miloš?');     // "sta-ima-milos"
$user = before('john@site.com', '@'); // "john"
$match = rx_match('^(?<user>[^@]+)@(?<host>.+)$', 'john@site.com');
$user = $match->group('user');        // "john"
```

## Router Component Integration Examples

### RouteMatcher Pattern Compilation

**Before (preg_replace_callback):**

```php
$pattern = preg_replace_callback(
    '/\{([^}]+)\}/',
    static function ($matches) use ($constraints) {
        $param = $matches[1];
        $isOptional = str_ends_with($param, '?');
        $isWildcard = str_ends_with($param, '*');
        $paramName = preg_replace('/[?*]$/', '', $param);
        $constraint = $constraints[$paramName] ?? '[^/]+';
        $segment = "(?P<{$paramName}>{$constraint})";

        if ($isWildcard) {
            $segment = "(?P<{$paramName}>.*)";
        }

        if ($isOptional) {
            $segment = "(?:/{$segment})?";
        } else {
            $segment = "/{$segment}";
        }

        return $segment;
    },
    $template
);

return "~^{$pattern}$~";
```

**After (Text DSL):**

```php
use Avax\Text\Text;
use function Avax\Text\rx_replace_callback;

$compiled = Text::of($template)
    ->replaceRegexCallback('\{([^}]+)\}', function ($matches) use ($constraints) {
        $param = $matches[1];
        $isOptional = Text::of($param)->endsWith('?');
        $isWildcard = Text::of($param)->endsWith('*');
        $paramName = Text::of($param)->replaceRegex('[?*]$', '')->toString();
        $constraint = $constraints[$paramName] ?? '[^/]+';
        $segment = "(?P<{$paramName}>{$constraint})";

        if ($isWildcard) {
            $segment = "(?P<{$paramName}>.*)";
        }

        if ($isOptional) {
            $segment = "(?:/{$segment})?";
        } else {
            $segment = "/{$segment}";
        }

        return $segment;
    })
    ->ensurePrefix('^')
    ->ensureSuffix('$')
    ->toString();

return '#' . $compiled . '#';
```

### URL Generation in Helpers

**Before (preg_replace):**

```php
preg_replace("/\{{$key}(?:[?*]?)}/", $value, $path);
preg_replace('/\{[^}]+\}/', '', $path);
```

**After (DSL):**

```php
use function Avax\Text\rx_replace;

$path = rx_replace("\\{{$key}(?:[?*]?)}", $path, $value);
$path = rx_replace('\{[^}]+\}', $path, '');
```

### Route Path Validation

**Before (preg_match):**

```php
preg_match('#^/[A-Za-z0-9_.\\-/{}?*]*$#', $path);
preg_match_all('#\\{([^{}]+)\\}#', $path, $matches, PREG_OFFSET_CAPTURE);
```

**After (DSL):**

```php
use function Avax\Text\rx_test;
use function Avax\Text\rx_match;

$valid = rx_test('^/[A-Za-z0-9_.\\-/{}?*]*$', $path);
$matches = rx_match('\\{([^{}]+)\\}', $path);
```

## API Reference

### Text Methods

- `Text::of(string)` - Create instance
- `Text::fromNullable(?string, string)` - Create from nullable
- `->pipe(callable)` - Functional composition
- `->map(callable)` - Alias for pipe
- `->tap(callable)` - Side effects
- `->when(bool, callable, ?callable)` - Conditional
- `->trim(string)` - Trim characters
- `->replace(string, string)` - Simple replace
- `->replaceRegex(string, string, string)` - Regex replace
- `->replaceRegexCallback(string, callable, string)` - Callback replace
- `->matchRegex(string, string)` - Regex match
- `->testRegex(string, string)` - Regex test
- `->splitRegex(string, string)` - Regex split
- String operations: `before`, `after`, `between`, `startsWith`, `endsWith`, `contains`
- `->ensurePrefix(string)`, `->ensureSuffix(string)` - Ensure prefix/suffix
- `->stripPrefix(string)`, `->stripSuffix(string)` - Remove prefix/suffix
- `->limit(int, string)` - Truncate with suffix
- Type conversion: `->toInt(?int)`, `->toFloat(?float)`, `->toBool(?bool)`
- Casing: `->lower()`, `->slug(string)`, `->camel()`, `->snake(string)`, `->studly()`
- `->toAscii()` - Convert to ASCII
- `->collapseWhitespace()` - Normalize whitespace

### Pattern Methods

- `Pattern::of(string, string)` - Create with raw pattern and flags
- `->toPreg()` - Convert to preg-compatible string
- `->test(string)` - Test if matches
- `->match(string)` - Get MatchResult
- `->replace(string, string)` - Replace occurrences
- `->replaceCallback(string, callable)` - Replace with callback
- `->split(string, int, int)` - Split by pattern

### MatchResult Methods

- `->matched` - Bool if matched
- `->matches` - Raw matches array
- `->group(string)` - Get named group
- `->namedGroups()` - Get all named groups
- `->fullMatch()` - Get full match

### Functional Helpers

- `text(string)` - Create Text
- `t(?string, string)` - Create Text from nullable
- `pipe(string, callable)` - Functional pipe
- `trimmed(string)`, `slug(string)`, `camel(string)`, `snake(string)`
- `limit(string, int, string)`, `before(string, string)`, `after(string, string)`
- `between(string, string, string)`
- `ensure_prefix(string, string)`, `ensure_suffix(string, string)`
- `rx(string, string)` - Create Pattern
- `rx_test(string, string, string)` - Test pattern
- `rx_match(string, string, string)` - Match pattern
- `rx_replace(string, string, string, string)` - Replace pattern
- `rx_replace_callback(string, string, callable, string)` - Replace with callback
- `rx_split(string, string, string)` - Split by pattern

## Migration Guide

Replace preg_* calls with DSL equivalents:

| Old                                 | New                               |
|-------------------------------------|-----------------------------------|
| `preg_match($p, $s, $m)`            | `rx_match($p, $s)->matches`       |
| `preg_replace($p, $r, $s)`          | `rx_replace($p, $s, $r)`          |
| `preg_replace_callback($p, $f, $s)` | `rx_replace_callback($p, $s, $f)` |
| `preg_split($p, $s)`                | `rx_split($p, $s)`                |

Benefits:

- ✅ Immutable, chainable API
- ✅ Type-safe results
- ✅ Comprehensive error handling
- ✅ Functional programming support
- ✅ PSR-12 compliant