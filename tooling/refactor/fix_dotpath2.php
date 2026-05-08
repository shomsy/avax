<?php
$f = '/home/shomsy/projects/avax/components/DataStack/Data/System/Capabilities/DataPaths/DotPath.php';
$c = file_get_contents($f);

// Fix getKey - end() can return false
$c = str_replace(
    "return end(array: \$segments);",
    "\$last = end(array: \$segments);\n\n        return \$last !== false ? \$last : '';",
    $c
);

// Add @param for getValue
$c = str_replace(
    "public function getValue(array \$items, mixed \$default = null): mixed",
    "/**\n     * @param array<array-key, mixed> \$items\n     */\n    public function getValue(array \$items, mixed \$default = null): mixed",
    $c
);

// Add @param for setValue
$c = str_replace(
    "public function setValue(array &\$items, mixed \$value): void",
    "/**\n     * @param array<array-key, mixed> \$items\n     */\n    public function setValue(array &\$items, mixed \$value): void",
    $c
);

// Fix setValue array_shift null
$c = str_replace(
    "\$current[array_shift(array: \$keys)] = \$value;",
    "\$lastKey = array_shift(array: \$keys);\n        if (\$lastKey !== null) {\n            \$current[\$lastKey] = \$value;\n        }",
    $c
);

// Add @param for unsetValue
$c = str_replace(
    "public function unsetValue(array &\$items): bool",
    "/**\n     * @param array<array-key, mixed> \$items\n     */\n    public function unsetValue(array &\$items): bool",
    $c
);

// Fix unsetValue array_shift null
$c = str_replace(
    "unset(\$current[array_shift(array: \$keys)]);",
    "\$lastKey = array_shift(array: \$keys);\n        if (\$lastKey !== null) {\n            unset(\$current[\$lastKey]);\n        }",
    $c
);

// Add @param for exists
$c = str_replace(
    "public function exists(array \$items): bool",
    "/**\n     * @param array<array-key, mixed> \$items\n     */\n    public function exists(array \$items): bool",
    $c
);

file_put_contents($f, $c);
echo "Fixed DotPath\n";
