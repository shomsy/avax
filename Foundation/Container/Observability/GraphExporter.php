<?php

declare(strict_types=1);

namespace Avax\Container\Observability;

use JsonException;

/**
 * Renders structural graph artifacts into machine-readable and human-readable formats.
 */
final class GraphExporter
{
    /**
     * @param array<string, mixed> $artifact
     */
    public function export(array $artifact, string $format = 'json') : string
    {
        return match (strtolower(trim($format))) {
            'json' => $this->toJson(artifact: $artifact),
            'mermaid' => $this->toMermaid(artifact: $artifact),
            'dot', 'graphviz' => $this->toDot(artifact: $artifact),
            default => $this->toJson(artifact: $artifact),
        };
    }

    /**
     * @param array<string, mixed> $artifact
     */
    private function toJson(array $artifact) : string
    {
        try {
            return (string) json_encode($artifact, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return '{}';
        }
    }

    /**
     * @param array<string, mixed> $artifact
     */
    private function toMermaid(array $artifact) : string
    {
        $lines = ['flowchart LR'];
        $nodeIds = [];

        foreach ($artifact['nodes'] ?? [] as $node) {
            $label = (string) ($node['label'] ?? $node['id'] ?? 'node');
            $id = $this->nodeId(label: (string) ($node['id'] ?? $label), seen: $nodeIds);
            $lines[] = '    ' . $id . '["' . $this->escapeMermaid(label: $label) . '"]';
        }

        foreach ($artifact['edges'] ?? [] as $edge) {
            $from = $this->nodeId(label: (string) ($edge['from'] ?? 'from'), seen: $nodeIds);
            $to = $this->nodeId(label: (string) ($edge['to'] ?? 'to'), seen: $nodeIds);
            $label = trim((string) ($edge['label'] ?? ''));

            $lines[] = $label !== ''
                ? '    ' . $from . ' -->|' . $this->escapeMermaid(label: $label) . '| ' . $to
                : '    ' . $from . ' --> ' . $to;
        }

        return implode(PHP_EOL, $lines) . PHP_EOL;
    }

    /**
     * @param array<string, mixed> $artifact
     */
    private function toDot(array $artifact) : string
    {
        $lines = ['digraph container {', '  rankdir=LR;'];

        foreach ($artifact['nodes'] ?? [] as $node) {
            $id = $this->quote(value: (string) ($node['id'] ?? 'node'));
            $label = $this->quote(value: (string) ($node['label'] ?? $node['id'] ?? 'node'));
            $lines[] = '  ' . $id . ' [label=' . $label . '];';
        }

        foreach ($artifact['edges'] ?? [] as $edge) {
            $from = $this->quote(value: (string) ($edge['from'] ?? 'from'));
            $to = $this->quote(value: (string) ($edge['to'] ?? 'to'));
            $label = trim((string) ($edge['label'] ?? ''));

            $lines[] = $label !== ''
                ? '  ' . $from . ' -> ' . $to . ' [label=' . $this->quote(value: $label) . '];'
                : '  ' . $from . ' -> ' . $to . ';';
        }

        $lines[] = '}';

        return implode(PHP_EOL, $lines) . PHP_EOL;
    }

    /**
     * @param array<string, string> $seen
     */
    private function nodeId(string $label, array &$seen) : string
    {
        if (isset($seen[$label])) {
            return $seen[$label];
        }

        $seen[$label] = 'n' . substr(sha1($label), 0, 10);

        return $seen[$label];
    }

    private function quote(string $value) : string
    {
        return '"' . str_replace(['\\', '"'], ['\\\\', '\\"'], $value) . '"';
    }

    private function escapeMermaid(string $label) : string
    {
        return str_replace(['"', "\n", "\r"], ["'", ' ', ' '], $label);
    }
}
