#!/bin/bash
mkdir -p Code-Review-And-ToDo

echo "# Scattered Structure Review" > Code-Review-And-ToDo/scattered-structure-review.md

echo "## find components -maxdepth 4 -type d | sort" >> Code-Review-And-ToDo/scattered-structure-review.md
echo "\`\`\`" >> Code-Review-And-ToDo/scattered-structure-review.md
find components -maxdepth 4 -type d | sort >> Code-Review-And-ToDo/scattered-structure-review.md
echo "\`\`\`" >> Code-Review-And-ToDo/scattered-structure-review.md

echo "## find components/DataFoundation -type f | sort" >> Code-Review-And-ToDo/scattered-structure-review.md
echo "\`\`\`" >> Code-Review-And-ToDo/scattered-structure-review.md
find components/DataFoundation -type f 2>/dev/null | sort >> Code-Review-And-ToDo/scattered-structure-review.md
echo "\`\`\`" >> Code-Review-And-ToDo/scattered-structure-review.md

echo "## find components/DataLayer -type f | sort" >> Code-Review-And-ToDo/scattered-structure-review.md
echo "\`\`\`" >> Code-Review-And-ToDo/scattered-structure-review.md
find components/DataLayer -type f 2>/dev/null | sort >> Code-Review-And-ToDo/scattered-structure-review.md
echo "\`\`\`" >> Code-Review-And-ToDo/scattered-structure-review.md

echo "## find components/Data -type f | sort" >> Code-Review-And-ToDo/scattered-structure-review.md
echo "\`\`\`" >> Code-Review-And-ToDo/scattered-structure-review.md
find components/Data -type f 2>/dev/null | sort >> Code-Review-And-ToDo/scattered-structure-review.md
echo "\`\`\`" >> Code-Review-And-ToDo/scattered-structure-review.md

echo "## find components/Persistence -type f | sort" >> Code-Review-And-ToDo/scattered-structure-review.md
echo "\`\`\`" >> Code-Review-And-ToDo/scattered-structure-review.md
find components/Persistence -type f 2>/dev/null | sort >> Code-Review-And-ToDo/scattered-structure-review.md
echo "\`\`\`" >> Code-Review-And-ToDo/scattered-structure-review.md

echo "## grep -R \"namespace components\\\\\" -n components" >> Code-Review-And-ToDo/scattered-structure-review.md
echo "\`\`\`" >> Code-Review-And-ToDo/scattered-structure-review.md
grep -R "namespace components\\\\" -n components 2>/dev/null >> Code-Review-And-ToDo/scattered-structure-review.md
echo "\`\`\`" >> Code-Review-And-ToDo/scattered-structure-review.md

echo "## grep -R \"namespace Avax\\\\DataFoundation\" -n components" >> Code-Review-And-ToDo/scattered-structure-review.md
echo "\`\`\`" >> Code-Review-And-ToDo/scattered-structure-review.md
grep -R "namespace Avax\\\\DataFoundation" -n components 2>/dev/null >> Code-Review-And-ToDo/scattered-structure-review.md
echo "\`\`\`" >> Code-Review-And-ToDo/scattered-structure-review.md

echo "## grep -R \"namespace Avax\\\\DataLayer\" -n components" >> Code-Review-And-ToDo/scattered-structure-review.md
echo "\`\`\`" >> Code-Review-And-ToDo/scattered-structure-review.md
grep -R "namespace Avax\\\\DataLayer" -n components 2>/dev/null >> Code-Review-And-ToDo/scattered-structure-review.md
echo "\`\`\`" >> Code-Review-And-ToDo/scattered-structure-review.md

echo "## grep -R \"describeResponsibility\" -n components" >> Code-Review-And-ToDo/scattered-structure-review.md
echo "\`\`\`" >> Code-Review-And-ToDo/scattered-structure-review.md
grep -R "describeResponsibility" -n components 2>/dev/null >> Code-Review-And-ToDo/scattered-structure-review.md
echo "\`\`\`" >> Code-Review-And-ToDo/scattered-structure-review.md

echo "## grep -R \"addslashes\" -n components framework" >> Code-Review-And-ToDo/scattered-structure-review.md
echo "\`\`\`" >> Code-Review-And-ToDo/scattered-structure-review.md
grep -R "addslashes" -n components framework 2>/dev/null >> Code-Review-And-ToDo/scattered-structure-review.md
echo "\`\`\`" >> Code-Review-And-ToDo/scattered-structure-review.md

echo "## grep -R \"toInsertSql\" -n components framework" >> Code-Review-And-ToDo/scattered-structure-review.md
echo "\`\`\`" >> Code-Review-And-ToDo/scattered-structure-review.md
grep -R "toInsertSql" -n components framework 2>/dev/null >> Code-Review-And-ToDo/scattered-structure-review.md
echo "\`\`\`" >> Code-Review-And-ToDo/scattered-structure-review.md
