<?php

declare(strict_types=1);

namespace Avax\Components\Application\Container\System\Capabilities\Diagnostics\Observability;

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
        return match (strtolower(string: trim(string: $format))) {
            'mermaid'         => $this->toMermaid(artifact: $artifact),
            'dot', 'graphviz' => $this->toDot(artifact: $artifact),
            'html'            => $this->toHtml(artifact: $artifact),
            default           => $this->toJson(artifact: $artifact),
        };
    }

    /**
     * @param array<string, mixed> $artifact
     */
    private function toMermaid(array $artifact) : string
    {
        $lines   = ['flowchart LR'];
        $nodeIds = [];

        foreach ($artifact['nodes'] ?? [] as $node) {
            $label   = (string) ($node['label'] ?? $node['id'] ?? 'node');
            $id      = $this->nodeId(label: (string) ($node['id'] ?? $label), seen: $nodeIds);
            $lines[] = '    ' . $id . '["' . $this->escapeMermaid(label: $label) . '"]';
        }

        foreach ($artifact['edges'] ?? [] as $edge) {
            $from  = $this->nodeId(label: (string) ($edge['from'] ?? 'from'), seen: $nodeIds);
            $to    = $this->nodeId(label: (string) ($edge['to'] ?? 'to'), seen: $nodeIds);
            $label = trim(string: (string) ($edge['label'] ?? ''));

            $lines[] = $label !== ''
                ? '    ' . $from . ' -->|' . $this->escapeMermaid(label: $label) . '| ' . $to
                : '    ' . $from . ' --> ' . $to;
        }

        return implode(separator: PHP_EOL, array: $lines) . PHP_EOL;
    }

    /**
     * @param array<string, string> $seen
     */
    private function nodeId(string $label, array &$seen) : string
    {
        if (isset($seen[$label])) {
            return $seen[$label];
        }

        $seen[$label] = 'n' . substr(string: sha1(string: $label), offset: 0, length: 10);

        return $seen[$label];
    }

    private function escapeMermaid(string $label) : string
    {
        return str_replace(search: ['"', "\n", "\r"], replace: ["'", ' ', ' '], subject: $label);
    }

    /**
     * @param array<string, mixed> $artifact
     */
    private function toDot(array $artifact) : string
    {
        $lines = ['digraph container {', '  rankdir=LR;'];

        foreach ($artifact['nodes'] ?? [] as $node) {
            $id      = $this->quote(value: (string) ($node['id'] ?? 'node'));
            $label   = $this->quote(value: (string) ($node['label'] ?? $node['id'] ?? 'node'));
            $lines[] = '  ' . $id . ' [label=' . $label . '];';
        }

        foreach ($artifact['edges'] ?? [] as $edge) {
            $from  = $this->quote(value: (string) ($edge['from'] ?? 'from'));
            $to    = $this->quote(value: (string) ($edge['to'] ?? 'to'));
            $label = trim(string: (string) ($edge['label'] ?? ''));

            $lines[] = $label !== ''
                ? '  ' . $from . ' -> ' . $to . ' [label=' . $this->quote(value: $label) . '];'
                : '  ' . $from . ' -> ' . $to . ';';
        }

        $lines[] = '}';

        return implode(separator: PHP_EOL, array: $lines) . PHP_EOL;
    }

    private function quote(string $value) : string
    {
        return '"' . str_replace(search: ['\\', '"'], replace: ['\\\\', '\\"'], subject: $value) . '"';
    }

    /**
     * @param array<string, mixed> $artifact
     */
    private function toHtml(array $artifact) : string
    {
        $json = $this->toJson(artifact: $artifact);

        $template = <<<'HTML'
            <!doctype html>
            <html lang="en">
            <head>
              <meta charset="utf-8">
              <title>Container Graph Explorer</title>
              <style>
                :root {
                  color-scheme: light;
                  --bg: #f6f4ef;
                  --panel: #fffdf8;
                  --line: #d6d0c2;
                  --text: #1e1f1a;
                  --accent: #0f6b63;
                  --muted: #716a5d;
                }
                * { box-sizing: border-box; }
                body {
                  margin: 0;
                  font: 14px/1.5 ui-monospace, SFMono-Regular, Menlo, monospace;
                  color: var(--text);
                  background: linear-gradient(180deg, #fbfaf6 0%, var(--bg) 100%);
                }
                header {
                  padding: 18px 22px;
                  border-bottom: 1px solid var(--line);
                  display: flex;
                  justify-content: space-between;
                  align-items: baseline;
                  gap: 12px;
                }
                header h1 {
                  margin: 0;
                  font-size: 18px;
                }
                header .meta { color: var(--muted); }
                main {
                  display: grid;
                  grid-template-columns: 340px 1fr;
                  min-height: calc(100vh - 67px);
                }
                aside, section {
                  padding: 18px 22px;
                }
                aside {
                  border-right: 1px solid var(--line);
                  background: rgba(255,255,255,0.5);
                }
                input {
                  width: 100%;
                  padding: 10px 12px;
                  border: 1px solid var(--line);
                  background: var(--panel);
                }
                .list {
                  margin-top: 14px;
                  max-height: calc(100vh - 180px);
                  overflow: auto;
                  border: 1px solid var(--line);
                  background: var(--panel);
                }
                .node {
                  padding: 10px 12px;
                  border-bottom: 1px solid var(--line);
                  cursor: pointer;
                }
                .node:last-child { border-bottom: 0; }
                .node.active {
                  background: #e5f4f2;
                  color: var(--accent);
                }
                .badge {
                  display: inline-block;
                  margin-left: 6px;
                  padding: 1px 6px;
                  font-size: 11px;
                  border: 1px solid var(--line);
                  border-radius: 999px;
                  color: var(--muted);
                }
                #canvas {
                  width: 100%;
                  height: 420px;
                  display: block;
                  border: 1px solid var(--line);
                  background: var(--panel);
                }
                .columns {
                  display: grid;
                  grid-template-columns: 1fr 1fr;
                  gap: 16px;
                  margin-top: 16px;
                }
                .panel {
                  border: 1px solid var(--line);
                  background: var(--panel);
                  padding: 14px;
                  min-height: 180px;
                  overflow: auto;
                }
                pre {
                  margin: 0;
                  white-space: pre-wrap;
                  word-break: break-word;
                }
              </style>
            </head>
            <body>
              <header>
                <h1>Container Graph Explorer</h1>
                <div class="meta" id="summary"></div>
              </header>
              <main>
                <aside>
                  <input id="search" type="search" placeholder="Filter nodes">
                  <div class="list" id="nodes"></div>
                </aside>
                <section>
                  <svg id="canvas" viewBox="0 0 920 420" preserveAspectRatio="xMidYMid meet"></svg>
                  <div class="columns">
                    <div class="panel"><pre id="details"></pre></div>
                    <div class="panel"><pre id="edges"></pre></div>
                  </div>
                </section>
              </main>
              <script id="graph-data" type="application/json">__GRAPH_DATA__</script>
              <script>
                const artifact = JSON.parse(document.getElementById('graph-data').textContent || '{}');
                const nodes = artifact.nodes || [];
                const edges = artifact.edges || [];
                const search = document.getElementById('search');
                const list = document.getElementById('nodes');
                const details = document.getElementById('details');
                const edgePanel = document.getElementById('edges');
                const canvas = document.getElementById('canvas');
                const summary = document.getElementById('summary');
            
                summary.textContent = `${artifact.kind || 'graph'} | ${artifact.scope || 'global'} | ${nodes.length} nodes | ${edges.length} edges`;
            
                const layout = nodes.map((node, index) => {
                  const cols = Math.max(1, Math.ceil(Math.sqrt(nodes.length || 1)));
                  const x = 80 + (index % cols) * 180;
                  const y = 70 + Math.floor(index / cols) * 110;
                  return { ...node, x, y };
                });
            
                const nodeMap = Object.fromEntries(layout.map((node) => [node.id, node]));
            
                function renderGraph(activeId = '') {
                  const edgeSvg = edges.map((edge) => {
                    const from = nodeMap[edge.from];
                    const to = nodeMap[edge.to];
                    if (!from || !to) return '';
                    const active = activeId !== '' && (edge.from === activeId || edge.to === activeId);
                    const color = active ? '#0f6b63' : '#a8a191';
                    return `<line x1="${from.x}" y1="${from.y}" x2="${to.x}" y2="${to.y}" stroke="${color}" stroke-width="${active ? 2.5 : 1.2}" />`;
                  }).join('');
            
                  const nodeSvg = layout.map((node) => {
                    const active = node.id === activeId;
                    const fill = active ? '#0f6b63' : '#fffdf8';
                    const stroke = active ? '#0f6b63' : '#5d584d';
                    const text = active ? '#fffdf8' : '#1e1f1a';
                    return `
                      <g data-node="${node.id}">
                        <rect x="${node.x - 58}" y="${node.y - 18}" width="116" height="36" rx="10" fill="${fill}" stroke="${stroke}" />
                        <text x="${node.x}" y="${node.y + 5}" fill="${text}" font-size="11" text-anchor="middle">${node.label || node.id}</text>
                      </g>
                    `;
                  }).join('');
            
                  canvas.innerHTML = edgeSvg + nodeSvg;
                }
            
                function nodeItem(node, active = false) {
                  const badge = node.type ? `<span class="badge">${node.type}</span>` : '';
                  return `<div class="node ${active ? 'active' : ''}" data-id="${node.id}">${node.label || node.id}${badge}</div>`;
                }
            
                function showNode(activeId = '') {
                  const query = (search.value || '').toLowerCase();
                  const visible = layout.filter((node) => {
                    const label = `${node.id} ${node.label || ''}`.toLowerCase();
                    return query === '' || label.includes(query);
                  });
            
                  list.innerHTML = visible.map((node) => nodeItem(node, node.id === activeId)).join('');
                  renderGraph(activeId);
            
                  if (activeId === '') {
                    details.textContent = JSON.stringify(artifact.meta || {}, null, 2);
                    edgePanel.textContent = JSON.stringify(edges, null, 2);
                    return;
                  }
            
                  const node = nodeMap[activeId];
                  const relatedEdges = edges.filter((edge) => edge.from === activeId || edge.to === activeId);
                  details.textContent = JSON.stringify(node || {}, null, 2);
                  edgePanel.textContent = JSON.stringify(relatedEdges, null, 2);
                }
            
                list.addEventListener('click', (event) => {
                  const target = event.target.closest('.node');
                  if (!target) return;
                  showNode(target.dataset.id || '');
                });
            
                search.addEventListener('input', () => showNode(''));
                showNode('');
              </script>
            </body>
            </html>
            HTML;

        return str_replace(search: '__GRAPH_DATA__', replace: $json, subject: $template);
    }

    /**
     * @param array<string, mixed> $artifact
     */
    private function toJson(array $artifact) : string
    {
        try {
            return (string) json_encode(value: $artifact, flags: JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return '{}';
        }
    }
}
