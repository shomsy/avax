#!/bin/bash

echo "🧹 Purging leaked AI chat logs and meta-files from runtime directories..."

# Pronalazi i briše sve .md, .txt, .bak fajlove unutar components i framework, IGNORIŠUĆI README.md
find components framework -type f \( -name "*.md" -o -name "*.txt" -o -name "*.bak" \) -not -iname "readme.md" -exec rm -v {} \;

echo "✨ Runtime codebase is now pure!"
