<?php

declare(strict_types=1);

namespace Avax\Tooling;

use PhpParser\Node;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Instanceof_;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\IntersectionType;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\NullableType;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Catch_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\GroupUse;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Node\UnionType;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use PhpParser\ParserFactory;

require_once dirname(__DIR__) . '/vendor/autoload.php';

$baseDir = dirname(__DIR__);
$parser = new ParserFactory()->createForNewestSupportedVersion();
$externalPrefixes = loadExternalPrefixes($baseDir);

/** @var list<string> $phpFiles */
$phpFiles = [];
foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($baseDir)) as $file) {
    if (!$file instanceof SplFileInfo) {
        continue;
    }

    if (!$file->isFile()) {
        continue;
    }

    if ($file->getExtension() !== 'php') {
        continue;
    }

    $path = $file->getPathname();
    if (str_contains((string)$path, '/vendor/')) {
        continue;
    }

    if (str_contains((string)$path, '/audit_broken_refs.php')) {
        continue;
    }

    $phpFiles[] = $path;
}

$defined = [];
$references = [];
$total = count($phpFiles);

echo "PASS 1: Parsing {$total} files for definitions...\n";

foreach ($phpFiles as $idx => $path) {
    if (($idx + 1) % 500 === 0) {
        echo '  ' . ($idx + 1) . sprintf(' / %d%s', $total, PHP_EOL);
    }

    try {
        $content = file_get_contents($path);
        if ($content === false) {
            continue;
        }

        $stmts = $parser->parse($content);
    } catch (Exception) {
        continue;
    }

    if (!$stmts) {
        continue;
    }

    $ns = '';
    foreach ($stmts as $stmt) {
        if ($stmt instanceof Namespace_) {
            $ns = $stmt->name instanceof Name ? $stmt->name->toString() : '';
            foreach ($stmt->stmts as $inner) {
                if ($inner instanceof ClassLike && isset($inner->name)) {
                    $fqn = $ns !== '' && $ns !== '0' ? $ns . '\\' . $inner->name->toString() : $inner->name->toString();
                    $defined[$fqn] = $path;
                }
            }
        } elseif ($stmt instanceof ClassLike && isset($stmt->name)) {
            $fqn = $ns !== '' && $ns !== '0' ? $ns . '\\' . $stmt->name->toString() : $stmt->name->toString();
            $defined[$fqn] = $path;
        }
    }
}

echo 'PASS 1 done. Defined: ' . count($defined) . "\n";

/**
 * @param array<string, string> $uses
 */
function resolveName(Name $name, string $ns, array $uses): string
{
    // PHP-Parser v5: name->name is the string representation
    $nameStr = $name->name;
    if (isBuiltin($nameStr)) {
        return $nameStr;
    }

    if ($name instanceof FullyQualified) {
        return $nameStr;
    }

    $parts = explode('\\', $nameStr);
    $first = $parts[0];
    if (isset($uses[$first])) {
        $parts[0] = $uses[$first];

        return implode('\\', $parts);
    }

    return $ns !== '' && $ns !== '0' ? $ns . '\\' . $nameStr : $nameStr;
}

function isBuiltin(string $name): bool
{
    return in_array(strtolower($name), ['self', 'static', 'parent', 'true', 'false', 'null', 'array', 'callable', 'int', 'float', 'string', 'bool', 'object', 'mixed', 'iterable', 'void', 'never', 'attribute', 'stdclass'], true);
}

/**
 * @return list<string>
 */
function loadExternalPrefixes(string $baseDir): array
{
    $prefixes = ['Psr\\'];
    $autoloadPsr4 = $baseDir . '/vendor/composer/autoload_psr4.php';
    $autoloadNamespaced = $baseDir . '/vendor/composer/autoload_namespaces.php';
    $autoloadClassmap = $baseDir . '/vendor/composer/autoload_classmap.php';

    foreach ([$autoloadPsr4, $autoloadNamespaced] as $autoloadFile) {
        if (!is_file($autoloadFile)) {
            continue;
        }

        $loaded = require $autoloadFile;
        if (!is_array($loaded)) {
            continue;
        }

        foreach (array_keys($loaded) as $prefix) {
            if (is_string($prefix) && $prefix !== '' && !str_starts_with($prefix, 'Avax\\')) {
                $prefixes[] = $prefix;
            }
        }
    }

    if (is_file($autoloadClassmap)) {
        $loaded = require $autoloadClassmap;
        if (is_array($loaded)) {
            foreach (array_keys($loaded) as $className) {
                if (!is_string($className)) {
                    continue;
                }

                if ($className === '') {
                    continue;
                }

                if (str_starts_with($className, 'Avax\\')) {
                    continue;
                }

                if (str_starts_with($className, 'components\\')) {
                    continue;
                }

                $parts = explode('\\', $className);
                if (count($parts) > 1) {
                    $prefixes[] = $parts[0] . '\\';
                }
            }
        }
    }

    return array_values(array_unique($prefixes));
}

/**
 * @param array<string, string> $defined
 * @param list<string> $externalPrefixes
 * @param array<string, list<array{file: string, context: string, line: int}>> $references
 */
function addRef(string $fqn, string $file, string $ctx, int $line, array &$references, array $defined, array $externalPrefixes): void
{
    $fqn = trim($fqn, '\\');
    if ($fqn === '' || isBuiltin($fqn)) {
        return;
    }

    if (isset($defined[$fqn])) {
        return;
    }

    if (
        class_exists($fqn, false)
        || interface_exists($fqn, false)
        || trait_exists($fqn, false)
        || (function_exists('enum_exists') && enum_exists($fqn, false))
    ) {
        return;
    }

    foreach ($externalPrefixes as $externalPrefix) {
        if (str_starts_with($fqn, $externalPrefix)) {
            return;
        }
    }

    if (!str_contains($fqn, '\\')) {
        $globals = ['arrayiterator', 'runtimeexception', 'invalidargumentexception', 'logicexception', 'exception', 'throwable', 'datetime', 'datetimeimmutable', 'dateinterval', 'closure', 'generator', 'arrayobject', 'splfileinfo', 'splfileobject', 'countable', 'iterator', 'iteratoraggregate', 'arrayaccess', 'serializable', 'jsonserializable', 'traversable', 'seekableiterator', 'recursiveiterator', 'pdostatement', 'pdoexception', 'reflectionclass', 'reflectionfunction', 'reflectionmethod', 'reflectionproperty', 'reflectionparameter', 'reflector', 'phpunit_framework_testcase', 'testcase'];
        if (in_array(strtolower($fqn), $globals, true)) {
            return;
        }
    }

    $references[$fqn][] = ['file' => $file, 'context' => $ctx, 'line' => $line];
}

/**
 * @param array<string, string> $uses
 * @param array<string, list<array{file: string, context: string, line: int}>> $references
 * @param array<string, string> $defined
 * @param list<string> $externalPrefixes
 */
function processType(?Node $node, string $ns, array $uses, string $file, string $ctx, int $line, array &$references, array $defined, array $externalPrefixes): void
{
    if (!$node instanceof Node) {
        return;
    }

    if ($node instanceof Name) {
        addRef(resolveName($node, $ns, $uses), $file, $ctx, $line, $references, $defined, $externalPrefixes);
    } elseif ($node instanceof UnionType || $node instanceof IntersectionType) {
        foreach ($node->types as $t) {
            processType($t, $ns, $uses, $file, $ctx, $line, $references, $defined, $externalPrefixes);
        }
    } elseif ($node instanceof NullableType) {
        processType($node->type, $ns, $uses, $file, $ctx, $line, $references, $defined, $externalPrefixes);
    }
}

class RefVisitor extends NodeVisitorAbstract
{
    private string $ns = '';

    /** @var array<string, string> */
    private array $uses = [];

    /** @var array<string, list<array{file: string, context: string, line: int}>> */
    private array $references;

    /**
     * @param array<string, string> $defined
     * @param list<string> $externalPrefixes
     * @param array<string, list<array{file: string, context: string, line: int}>> $references
     */
    public function __construct(private readonly string $file, private readonly array $defined, private readonly array $externalPrefixes, array &$references)
    {
        $this->references = &$references;
    }

    public function enterNode(Node $node): ?int
    {
        if ($node instanceof Namespace_) {
            $this->ns = $node->name instanceof Name ? $node->name->toString() : '';
            $this->uses = [];
        } elseif ($node instanceof Use_) {
            if ($node->type === Use_::TYPE_NORMAL) {
                foreach ($node->uses as $use) {
                    $alias = $use->getAlias()->toString();
                    $this->uses[$alias] = $use->name->toString();
                    $this->add($use->name->toString(), 'use-statement', $node->getStartLine());
                }
            }
        } elseif ($node instanceof GroupUse) {
            if ($node->type === Use_::TYPE_NORMAL) {
                $prefix = $node->prefix->toString();
                foreach ($node->uses as $use) {
                    $fqn = $prefix . '\\' . $use->name->toString();
                    $alias = $use->getAlias()->toString();
                    $this->uses[$alias] = $fqn;
                    $this->add($fqn, 'use-statement', $node->getStartLine());
                }
            }
        } elseif ($node instanceof ClassLike) {
            // Implements (classes and enums)
            if (property_exists($node, 'implements') && $node->implements !== null) {
                foreach ($node->implements as $impl) {
                    $this->add($this->resolve($impl), 'implements', $node->getStartLine());
                }
            }

            // Extends (classes and interfaces - in v5 both use 'extends')
            if (property_exists($node, 'extends') && $node->extends !== null && $node->extends) {
                $ext = $node->extends;
                if ($ext instanceof Name) {
                    $this->add($this->resolve($ext), 'extends', $node->getStartLine());
                } elseif (is_array($ext)) {
                    foreach ($ext as $e) {
                        if ($e instanceof Name) {
                            $this->add($this->resolve($e), 'extends', $node->getStartLine());
                        }
                    }
                }
            }

            foreach ($node->attrGroups as $ag) {
                foreach ($ag->attrs as $attr) {
                    $this->add($this->resolve($attr->name), 'attribute', $attr->getStartLine());
                }
            }
        } elseif ($node instanceof Function_ || $node instanceof ClassMethod) {
            foreach ($node->params as $param) {
                $ctx = ($node instanceof ClassMethod && $node->name->toString() === '__construct') ? 'constructor-param' : 'param-type';
                $this->processType($param->type, $ctx, $param->getStartLine());
                if ($param->default instanceof ClassConstFetch && $param->default->class instanceof Name) {
                    $this->add($this->resolve($param->default->class), 'class-const-fetch', $param->default->getStartLine());
                }

                if ($param->default instanceof New_ && $param->default->class instanceof Name) {
                    $this->add($this->resolve($param->default->class), 'new', $param->default->getStartLine());
                }
            }

            $this->processType($node->returnType, 'return-type', $node->getStartLine());
            foreach ($node->attrGroups as $ag) {
                foreach ($ag->attrs as $attr) {
                    $this->add($this->resolve($attr->name), 'attribute', $attr->getStartLine());
                }
            }
        } elseif ($node instanceof Property) {
            foreach ($node->props as $prop) {
                $this->processType($node->type, 'property-type', $prop->getStartLine());
            }

            foreach ($node->attrGroups as $ag) {
                foreach ($ag->attrs as $attr) {
                    $this->add($this->resolve($attr->name), 'attribute', $attr->getStartLine());
                }
            }
        } elseif ($node instanceof Catch_) {
            foreach ($node->types as $t) {
                $this->add($this->resolve($t), 'catch', $node->getStartLine());
            }
        } elseif ($node instanceof Instanceof_) {
            if ($node->class instanceof Name) {
                $this->add($this->resolve($node->class), 'instanceof', $node->getStartLine());
            }
        } elseif ($node instanceof New_) {
            if ($node->class instanceof Name) {
                $this->add($this->resolve($node->class), 'new', $node->getStartLine());
            }
        } elseif ($node instanceof StaticCall) {
            if ($node->class instanceof Name) {
                $this->add($this->resolve($node->class), 'static-call', $node->getStartLine());
            }
        } elseif ($node instanceof ClassConstFetch) {
            if ($node->class instanceof Name) {
                $this->add($this->resolve($node->class), 'class-const-fetch', $node->getStartLine());
            }
        } elseif ($node instanceof Expression && $node->expr instanceof FuncCall && $node->expr->name instanceof Name && $node->expr->name->toLowerString() === 'class_alias') {
            $args = $node->expr->getArgs();
            if (count($args) >= 2 && $args[0]->value instanceof String_) {
                $this->add($args[0]->value->value, 'class_alias target', $node->getStartLine());
            }
        }

        return null;
    }

    private function add(string $fqn, string $ctx, int $line): void
    {
        addRef($fqn, $this->file, $ctx, $line, $this->references, $this->defined, $this->externalPrefixes);
    }

    private function resolve(Name $name): string
    {
        return resolveName($name, $this->ns, $this->uses);
    }

    private function processType(?Node $node, string $ctx, int $line): void
    {
        processType($node, $this->ns, $this->uses, $this->file, $ctx, $line, $this->references, $this->defined, $this->externalPrefixes);
    }
}

echo "PASS 2: Parsing {$total} files for references...\n";

foreach ($phpFiles as $idx => $path) {
    if (($idx + 1) % 500 === 0) {
        echo '  ' . ($idx + 1) . sprintf(' / %d%s', $total, PHP_EOL);
    }

    try {
        $content = file_get_contents($path);
        if ($content === false) {
            continue;
        }

        $stmts = $parser->parse($content);
    } catch (Exception) {
        continue;
    }

    if (!$stmts) {
        continue;
    }

    $traverser = new NodeTraverser();
    $traverser->addVisitor(new RefVisitor($path, $defined, $externalPrefixes, $references));
    $traverser->traverse($stmts);
}

// compat.php array-style aliases
$compatPath = $baseDir . '/components/compat.php';
if (file_exists($compatPath)) {
    $src = file_get_contents($compatPath);
    if ($src !== false && preg_match_all('/[\'"]([^\'"]+)[\'"]\s*=>\s*[\'"]([^\'"]+)[\'"]/', $src, $m)) {
        $counter = count($m[1]);
        for ($k = 0; $k < $counter; $k++) {
            $target = trim($m[2][$k], '\\');
            if (!isset($defined[$target])) {
                addRef($target, $compatPath, 'class_alias target', 0, $references, $defined, $externalPrefixes);
            }
        }
    }
}

// Deduplicate
foreach ($references as $fqn => $refs) {
    $seen = [];
    $uniq = [];
    foreach ($refs as $ref) {
        $k = $ref['file'] . '|' . $ref['context'] . '|' . $ref['line'];
        if (!isset($seen[$k])) {
            $seen[$k] = true;
            $uniq[] = $ref;
        }
    }

    $references[$fqn] = $uniq;
}

ksort($references);

echo "\n=== BROKEN REFERENCES AUDIT REPORT ===\n\n";
$severityCounts = ['CRITICAL' => 0, 'MINOR' => 0];
foreach ($references as $fqn => $refs) {
    $isCritical = array_any($refs, fn($r): bool => in_array($r['context'], ['extends', 'implements', 'class_alias target', 'catch', 'constructor-param', 'new']));

    $severity = $isCritical ? 'CRITICAL' : 'MINOR';
    $severityCounts[$severity]++;
    echo "MISSING: {$fqn}  [{$severity}]\n";
    foreach ($refs as $ref) {
        echo "  - {$ref['file']}:{$ref['line']}  ({$ref['context']})\n";
    }

    echo "\n";
}

echo "=== SUMMARY ===\n";
echo 'Defined: ' . count($defined) . "\n";
echo 'Missing: ' . count($references) . "\n";
echo '  CRITICAL: ' . $severityCounts['CRITICAL'] . "\n";
echo '  MINOR: ' . $severityCounts['MINOR'] . "\n";
