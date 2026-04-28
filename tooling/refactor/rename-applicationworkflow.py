#!/usr/bin/env python3
import os, re

base = 'components/ApplicationWorkflow/System'
for root, dirs, files in os.walk(base):
    for fname in files:
        if not fname.endswith('.php'): continue
        path = os.path.join(root, fname)
        with open(path, 'r') as f:
            content = f.read()
        orig = content

        if 'PublicSurface' in root:
            # Namespace: components\ApplicationWorkflow or Avax\ApplicationWorkflow -> Avax\Components\ApplicationWorkflow\System\PublicSurface
            content = re.sub(
                r'^namespace\s+(?:components|Avax)\\ApplicationWorkflow\s*;',
                'namespace Avax\\Components\\ApplicationWorkflow\\System\\PublicSurface;',
                content,
                flags=re.MULTILINE
            )
            # use statements: point to Capabilities\Saga
            content = re.sub(
                r'use\s+(?:Avax\\|components\\)ApplicationWorkflow\\Saga\\',
                'use Avax\\Components\\ApplicationWorkflow\\System\\Capabilities\\Saga\\',
                content
            )
        elif '/Capabilities/Saga' in root:
            content = re.sub(
                r'^namespace\s+(?:components|Avax)\\ApplicationWorkflow\\Saga\s*;',
                'namespace Avax\\Components\\ApplicationWorkflow\\System\\Capabilities\\Saga;',
                content,
                flags=re.MULTILINE
            )
            content = re.sub(
                r'use\s+(?:Avax\\|components\\)ApplicationWorkflow\\Saga\\',
                'use Avax\\Components\\ApplicationWorkflow\\System\\Flows\\Saga\\',
                content
            )
        elif re.search(r'/Flows/Saga/[^/]+/', root):
            # Determine group name
            parts = root.split(os.sep)
            try:
                i = parts.index('Flows')
                group = parts[i+2]  # Flows/Saga/<group>
            except (ValueError, IndexError):
                continue
            # Namespace
            pattern = r'^namespace\s+(?:Avax\\|components\\)ApplicationWorkflow\\Saga\\' + re.escape(group) + r'\s*;'
            repl = 'namespace Avax\\Components\\ApplicationWorkflow\\System\\Flows\\Saga\\' + group + ';'
            content = re.sub(pattern, repl, content, flags=re.MULTILINE)
            # Use statements
            content = re.sub(
                r'use\s+(?:Avax\\|components\\)ApplicationWorkflow\\Saga\\',
                'use Avax\\Components\\ApplicationWorkflow\\System\\Flows\\Saga\\',
                content
            )

        if content != orig:
            with open(path, 'w') as f:
                f.write(content)
            print(f'Updated: {os.path.relpath(path)}')
