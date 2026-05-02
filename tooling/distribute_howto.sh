#!/bin/bash

echo "📦 Distributing how-to files to their proper governance namespaces..."

# Create target directories based on AGENTS.md paths
mkdir -p .agents/.rules/governance/architecture
mkdir -p .agents/.rules/governance/standards/coding
mkdir -p .agents/.rules/governance/standards/review
mkdir -p .agents/.rules/governance/standards/documentation
mkdir -p .agents/.rules/governance/standards/testing

# Copy files to their specific domains
cp .agents/how-to/how-to-architecture.md .agents/.rules/governance/architecture/ 2>/dev/null
cp .agents/how-to/how-to-architecture-extension.md .agents/.rules/governance/architecture/ 2>/dev/null

cp .agents/how-to/how-to-coding-standards.md .agents/.rules/governance/standards/coding/ 2>/dev/null
cp .agents/how-to/how-to-clean-code.md .agents/.rules/governance/standards/coding/ 2>/dev/null
cp .agents/how-to/how-to-code-style.md .agents/.rules/governance/standards/coding/ 2>/dev/null

cp .agents/how-to/how-to-code-review.md .agents/.rules/governance/standards/review/ 2>/dev/null

cp .agents/how-to/how-to-document.md .agents/.rules/governance/standards/documentation/ 2>/dev/null

cp .agents/how-to/how-to-unit-test.md .agents/.rules/governance/standards/testing/ 2>/dev/null

echo "✅ All how-to files have been distributed properly!"
echo "Check .agents/.rules/governance/ to see the new structured layout."
