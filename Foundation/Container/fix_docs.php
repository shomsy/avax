<?php

$dir = __DIR__ . '/docs';
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
$regexs = [
    // Top level H3 replacements based on preceding H2
    '#^## Quick Summary\s*$.*?(^### For Humans: What This Means)\s*$#ms' => ' (Summary)',
    '#^## Terminology \(MANDATORY, EXPANSIVE\)\s*$.*?(^### For Humans: What This Means)\s*$#ms' => ' (Terms)',
    '#^## Think of It\s*$.*?(^### For Humans: What This Means)\s*$#ms' => ' (Think)',
    '#^## Story Example\s*$.*?(^### For Humans: What This Means)\s*$#ms' => ' (Story)',
    '#^## For Dummies\s*$.*?(^### For Humans: What This Means)\s*$#ms' => ' (Dummies)',
    '#^## How It Works \(Technical\)\s*$.*?(^### For Humans: What This Means)\s*$#ms' => ' (How)',
    '#^## Architecture Role\s*$.*?(^### For Humans: What This Means)\s*$#ms' => ' (Role)',
    '#^## Methods\s*$.*?(^### For Humans: What This Means)\s*$#ms' => ' (Methods)',
    '#^## Risks, Trade-offs & Recommended Practices\s*$.*?(^### For Humans: What This Means)\s*$#ms' => ' (Risks)',
    '#^## Related Files & Folders\s*$.*?(^### For Humans: What This Means)\s*$#ms' => ' (Related)',
    '#^## What This Folder Represents\s*$.*?(^### For Humans: What This Means)\s*$#ms' => ' (Represent)',
    '#^## What Belongs Here\s*$.*?(^### For Humans: What This Means)\s*$#ms' => ' (Belongs)',
    '#^## What Does NOT Belong Here\s*$.*?(^### For Humans: What This Means)\s*$#ms' => ' (Not Belongs)',
    '#^## How Files Collaborate\s*$.*?(^### For Humans: What This Means)\s*$#ms' => ' (Collaboration)',
];

// Helper to apply suffix if not already present
function appendSuffix($content, $headerPattern, $targetLinePattern, $suffix)
{
    return preg_replace_callback($headerPattern, function ($matches) use ($targetLinePattern, $suffix) {
        $section = $matches[0];
        // replace the target line within this section
        // search for strictly `### For Humans: What This Means` with optional trailing whitespace, but NO existing suffix
        $pattern = '/' . preg_quote($targetLinePattern, '/') . '\s*$/m';
        if (preg_match($pattern, $section)) {
            return preg_replace($pattern, $targetLinePattern . $suffix, $section, 1);
        }
        return $section;
    }, $content);
}

// Method-based replacements
function fixMethods($content)
{
    // Splits by "### Method:" to handle each method block
    $parts = preg_split('/(^### Method: .*$)/m', $content, -1, PREG_SPLIT_DELIM_CAPTURE);

    $newContent = '';

    // Part 0 is pre-methods or the first non-matching part
    $newContent .= $parts[0];

    // Loop through pairs of (Header, Body)
    for ($i = 1; $i < count($parts); $i += 2) {
        $header = $parts[$i]; // "### Method: foo()"
        $body = $parts[$i + 1] ?? '';

        // Extract method name from header, e.g. "### Method: foo(args): ret" -> "foo"
        // Simple extraction: purely valid characters for a method name? or simple match
        // Header is "### Method: methodName(params): ret"
        if (preg_match('/^### Method: ([a-zA-Z0-9_]+)/', $header, $m)) {
            $methodName = $m[1];
        } else {
            $methodName = 'Unknown';
        }

        // 1. Fix "Technical Explanation"
        // Look for "#### Technical Explanation" EXACTLY (no suffix)
        $body = preg_replace('/^#### Technical Explanation\s*$/m', "#### Technical Explanation ($methodName)", $body);

        // 2. Fix "For Humans: What This Means" (level 5) inside technical explanation
        // It usually follows Technical Explanation.
        // Look for "##### For Humans: What This Means" EXACTLY
        $body = preg_replace('/^##### For Humans: What This Means\s*$/m', "##### For Humans: What This Means ($methodName)", $body);

        // 3. Fix repeated h5 headings within method blocks
        $body = preg_replace('/^##### Parameters\s*$/m', "##### Parameters ($methodName)", $body);
        $body = preg_replace('/^##### Returns\s*$/m', "##### Returns ($methodName)", $body);
        $body = preg_replace('/^##### Throws\s*$/m', "##### Throws ($methodName)", $body);
        $body = preg_replace('/^##### When to Use It\s*$/m', "##### When to Use It ($methodName)", $body);
        $body = preg_replace('/^##### Common Mistakes\s*$/m', "##### Common Mistakes ($methodName)", $body);

        $newContent .= $header . $body;
    }

    return $newContent;
}

foreach ($iterator as $file) {
    if ($file->isFile() && $file->getExtension() === 'md') {
        $content = file_get_contents($file->getPathname());
        $original = $content;

        // Apply Top Level Fixes
        // We iterate through sections by finding the H2 header and scanning until next H2?
        // Regex replacement with lookahead is tricky for "until next H2".
        // Instead, let's just use specific context matchers if possible, or simpler replacement.

        // Map of H2 Title -> Suffix
        $h2Map = [
            'Quick Summary' => ' (Summary)',
            'Terminology (MANDATORY, EXPANSIVE)' => ' (Terms)',
            'Think of It' => ' (Think)',
            'Story Example' => ' (Story)',
            'For Dummies' => ' (Dummies)',
            'How It Works (Technical)' => ' (How)',
            'Architecture Role' => ' (Role)',
            'Methods' => ' (Methods)',
            'Risks, Trade-offs & Recommended Practices' => ' (Risks)',
            'Related Files & Folders' => ' (Related)',
            'What This Folder Represents' => ' (Represent)',
            'What Belongs Here' => ' (Belongs)',
            'What Does NOT Belong Here' => ' (Not Belongs)',
            'How Files Collaborate' => ' (Collaboration)',
            'Why This Design (And Why Not Others)' => ' (Design)',
        ];

        foreach ($h2Map as $h2 => $suffix) {
            // Find matches of:
            // ## H2 ... (anything not containing another ##) ... ### For Humans: What This Means
            // We use `preg_replace_callback` on chunks starting with `## $h2`

            $escapedH2 = preg_quote($h2, '/');
            // Match start of line ## Title, then content until next ## or End of file
            // We use a lazy match .*? but we must ensure we don't cross into another ##
            // Actually, `###` is fine, but `## ` is boundary.

            // This regex matches the specific section.
            // Note: (?!^## ) ensures we don't match start of next section line
            $pattern = '/^## ' . $escapedH2 . '\s*$(?:(?!^## ).)*/ms';

            $content = preg_replace_callback($pattern, function ($matches) use ($suffix) {
                // inside this section, replace the specific H3 if it lacks suffix
                $text = $matches[0];
                $text = preg_replace('/^### For Humans: What This Means\s*$/m', '### For Humans: What This Means' . $suffix, $text);
                return $text;
            }, $content);
        }

        // Apply Method Level Fixes
        $content = fixMethods($content);

        if ($content !== $original) {
            file_put_contents($file->getPathname(), $content);
            echo "Fixed: " . $file->getPathname() . "\n";
        }
    }
}
