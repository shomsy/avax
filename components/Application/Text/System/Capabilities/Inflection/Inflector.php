<?php

declare(strict_types=1);

namespace Avax\Components\Application\Text\System\Capabilities\Inflection;

/**
 * Inflector provides word inflection (pluralization/singularization) with
 * configurable rules for irregular words and custom patterns.
 */
final class Inflector
{
    /** @var array<string, string> Irregular word mappings */
    private array $irregular
        = [
            'person'     => 'people', 'man' => 'men', 'woman' => 'women',
            'child'      => 'children', 'foot' => 'feet', 'tooth' => 'teeth',
            'goose'      => 'geese', 'mouse' => 'mice', 'ox' => 'oxen',
            'datum'      => 'data', 'medium' => 'media', 'analysis' => 'analyses',
            'basis'      => 'bases', 'crisis' => 'crises', 'diagnosis' => 'diagnoses',
            'ellipsis'   => 'ellipses', 'hypothesis' => 'hypotheses',
            'oasis'      => 'oases', 'parenthesis' => 'parentheses',
            'phenomenon' => 'phenomena', 'criterion' => 'criteria',
        ];

    /** @var array<string, array{pattern: string, replacement: string}> Pluralization rules */
    private array $pluralRules
        = [
            ['pattern' => '/(s|x|z|ch|sh)$/i', 'replacement' => '${1}es'],
            ['pattern' => '/([^aeiou])y$/i', 'replacement' => '${1}ies'],
            ['pattern' => '/(fe|f)$/i', 'replacement' => '${1}ves'],
        ];

    /** @var array<string, array{pattern: string, replacement: string}> Singularization rules */
    private array $singularRules
        = [
            ['pattern' => '/([^aeiou])ies$/i', 'replacement' => '${1}y'],
            ['pattern' => '/([sxz])es$/i', 'replacement' => '${1}'],
            ['pattern' => '/([a-z])s$/i', 'replacement' => '${1}'],
        ];

    public function addIrregular(string $singular, string $plural): void
    {
        $this->irregular[$singular] = $plural;
    }

    public function addPluralRule(string $pattern, string $replacement): void
    {
        array_unshift($this->pluralRules, ['pattern' => $pattern, 'replacement' => $replacement]);
    }

    public function addSingularRule(string $pattern, string $replacement): void
    {
        array_unshift($this->singularRules, ['pattern' => $pattern, 'replacement' => $replacement]);
    }

    public function pluralize(string $word, int $count = 2): string
    {
        if ($count === 1) {
            return $word;
        }

        $lower = strtolower($word);

        // Check irregular
        if (isset($this->irregular[$lower])) {
            return $this->preserveCase($word, $this->irregular[$lower]);
        }

        // Apply rules
        foreach ($this->pluralRules as $pluralRule) {
            $result = preg_replace($pluralRule['pattern'], $pluralRule['replacement'], $word);
            if ($result !== $word) {
                return (string) $result;
            }
        }

        return $word . 's';
    }

    private function preserveCase(string $original, string $replacement): string
    {
        if ($original === ucfirst($original)) {
            return ucfirst($replacement);
        }

        if ($original === strtoupper($original) && strlen($original) > 1) {
            return strtoupper($replacement);
        }

        return $replacement;
    }

    public function singularize(string $word): string
    {
        $lower = strtolower($word);

        // Check irregular (reverse lookup)
        $reverseIrregular = array_flip($this->irregular);
        if (isset($reverseIrregular[$lower])) {
            return $this->preserveCase($word, $reverseIrregular[$lower]);
        }

        // Apply rules
        foreach ($this->singularRules as $singularRule) {
            $result = preg_replace($singularRule['pattern'], $singularRule['replacement'], $word);
            if ($result !== $word) {
                return (string) $result;
            }
        }

        return $word;
    }

    public function camelCase(string $value): string
    {
        return lcfirst($this->studlyCase($value));
    }

    public function studlyCase(string $value): string
    {
        $words = preg_split('/[\s\-_]+/', $value);
        if ($words === false) {
            $words = [$value];
        }

        return implode('', array_map(static fn (string $w): string => ucfirst(strtolower($w)), $words));
    }

    public function kebabCase(string $value): string
    {
        return $this->snakeCase($value, '-');
    }

    public function snakeCase(string $value, string $delimiter = '_'): string
    {
        $value = preg_replace('/([a-z0-9])([A-Z])/', '$1' . $delimiter . '$2', $value);
        $value = preg_replace('/([A-Z]+)([A-Z][a-z])/', '$1' . $delimiter . '$2', (string) $value);
        $value = preg_replace('/[\s\-]+/', $delimiter, (string) $value);

        return strtolower((string) $value);
    }
}
