<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\PreCommit\Capabilities;

use Avax\Framework\System\Capabilities\PreCommit\Configuration\PreCommitConfig;
use Avax\Framework\System\Capabilities\PreCommit\Models\PreCommitIssue;

/**
 * Check How-To Rules
 *
 * Reads .agents/how-to/*.md files and validates code changes
 * comply with documented rules.
 */
final class CheckHowToRules implements CheckInterface
{
    /** @var array<string, array{file: string, forbidden_patterns: list<string>}> */
    private array $rules = [];

    public function __construct()
    {
        $this->loadRules();
    }

    private function loadRules() : void
    {
        $basePath = getcwd() ?: '.';
        $howToDir = $basePath . '/.agents/how-to';

        if (! is_dir($howToDir)) {
            return;
        }

        $files = glob($howToDir . '/how-to-*.md') ?: [];
        foreach ($files as $file) {
            $this->parseRuleFile($file);
        }
    }

    private function parseRuleFile(string $filePath) : void
    {
        $content = file_get_contents($filePath);
        if ($content === false) {
            return;
        }

        $fileName          = basename($filePath, '.md');
        $forbiddenPatterns = [];

        // Look for "Forbidden" or "Avoid" sections
        if (preg_match_all('/##\s+(?:Forbidden|Avoid|Must Not|Do Not)(?:\s+Patterns)?\s*\n(.*?)(?=\n##|\n#|\Z)/is', $content, $matches)) {
            foreach ($matches[1] as $section) {
                preg_match_all('/`([^`]+)`/', $section, $codeMatches);
                foreach ($codeMatches[1] as $code) {
                    if (! in_array(trim($code), ['', '0'], true)) {
                        $forbiddenPatterns[] = $code;
                    }
                }
            }
        }

        // Look for naming restrictions
        if (preg_match_all('/forbidden (?:name|names|pattern)?s?[\s:]+([A-Z][a-zA-Z,\s]+)/i', $content, $matches)) {
            foreach ($matches[1] as $match) {
                $names = preg_split('/[,\s]+/', $match) ?: [];
                foreach ($names as $name) {
                    $name = trim($name);
                    if ($name !== '' && $name !== '0') {
                        $forbiddenPatterns[] = $name;
                    }
                }
            }
        }

        $this->rules[$fileName] = [
            'file'               => $fileName,
            'forbidden_patterns' => array_values(array_unique($forbiddenPatterns)),
        ];
    }

    /**
     * @param array<string, mixed> $context
     *
     * @return list<PreCommitIssue>
     */
    public function run(array $context) : array
    {
        $issues = [];

        if ($this->rules === []) {
            return $issues;
        }

        $files    = is_array($context['files'] ?? null) ? $context['files'] : [];
        $basePath = is_string($context['base_path'] ?? null) ? $context['base_path'] : (getcwd() ?: '.');

        foreach ($files as $file) {
            if (! is_string($file)) {
                continue;
            }

            $filePath = $basePath . '/' . $file;
            if (! file_exists($filePath)) {
                continue;
            }

            if (! preg_match('/\.(php|js|ts|py|go)$/', $file)) {
                continue;
            }

            $content = file_get_contents($filePath);
            if ($content === false) {
                continue;
            }

            // Check against forbidden patterns
            foreach ($this->rules as $ruleName => $rule) {
                foreach ($rule['forbidden_patterns'] as $pattern) {
                    if (stripos($content, $pattern) !== false) {
                        // Skip if in comment
                        if ($this->isInComment($content, $pattern)) {
                            continue;
                        }

                        $issues[] = new PreCommitIssue(
                            'CheckHowToRules',
                            PreCommitIssue::SEVERITY_ERROR,
                            sprintf("Pattern '%s' from %s violates documented rule", $pattern, $ruleName),
                            $file,
                            null,
                            'HOWTO_RULE_' . strtoupper(substr($ruleName, -3))
                        );
                    }
                }
            }
        }

        return $issues;
    }

    private function isInComment(string $content, string $pattern) : bool
    {
        $pos = stripos($content, $pattern);
        if ($pos === false) {
            return false;
        }

        $before           = substr($content, 0, $pos);
        $lastBlockComment = strrpos($before, '/*');
        $lastLineComment  = strrpos($before, '//');
        $lastHashComment  = strrpos($before, '#');

        if ($lastBlockComment !== false) {
            $blockEnd = strpos($content, '*/', $lastBlockComment);
            if ($blockEnd !== false && $blockEnd > $pos) {
                return true;
            }
        }

        $lastNewline = strrpos($before, "\n");
        if ($lastLineComment !== false && ($lastNewline === false || $lastLineComment > $lastNewline)) {
            return true;
        }

        return $lastHashComment !== false && ($lastNewline === false || $lastHashComment > $lastNewline);
    }
}
