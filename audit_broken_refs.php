<?php
// Custom autoloader for PHP-Parser v5 only, avoiding project's vendor/autoload.php
spl_autoload_register(function ($class) {
    $prefix  = 'PhpParser\\';
    $baseDir = __DIR__ . '/vendor/nikic/php-parser/lib/PhpParser/';
    $len     = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) return;
    $relativeClass = substr($class, $len);
    $file          = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
    if (file_exists($file)) require $file;
});

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Stmt;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use PhpParser\ParserFactory;

$baseDir = __DIR__;
$parser  = (new ParserFactory)->createForNewestSupportedVersion();

$phpFiles = [];
foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($baseDir)) as $file) {
    if (! $file->isFile() || $file->getExtension() !== 'php') continue;
    $path = $file->getPathname();
    if (strpos($path, '/vendor/') !== false) continue;
    if (strpos($path, '/audit_broken_refs.php') !== false) continue;
    $phpFiles[] = $path;
}

$defined    = [];
$references = [];
$total      = count($phpFiles);

echo "PASS 1: Parsing $total files for definitions...\n";

foreach ($phpFiles as $idx => $path) {
    if (($idx + 1) % 500 === 0) echo "  " . ($idx + 1) . " / $total\n";
    try {
        $stmts = $parser->parse(file_get_contents($path));
    } catch (Exception $e) {
        continue;
    }
    if (! $stmts) continue;
    $ns = '';
    foreach ($stmts as $stmt) {
        if ($stmt instanceof Stmt\Namespace_) {
            $ns = $stmt->name ? $stmt->name->toString() : '';
            foreach ($stmt->stmts as $inner) {
                if ($inner instanceof Stmt\ClassLike && isset($inner->name)) {
                    $fqn           = $ns ? $ns . '\\' . $inner->name->toString() : $inner->name->toString();
                    $defined[$fqn] = $path;
                }
            }
        } elseif ($stmt instanceof Stmt\ClassLike && isset($stmt->name)) {
            $fqn           = $ns ? $ns . '\\' . $stmt->name->toString() : $stmt->name->toString();
            $defined[$fqn] = $path;
        }
    }
}

echo "PASS 1 done. Defined: " . count($defined) . "\n";

function resolveName(Node\Name $name, string $ns, array $uses) : string
{
    // PHP-Parser v5: name->name is the string representation
    $nameStr = $name->name;
    if ($name instanceof Node\Name\FullyQualified) return $nameStr;
    $parts = explode('\\', $nameStr);
    $first = $parts[0];
    if (isset($uses[$first])) {
        $parts[0] = $uses[$first];

        return implode('\\', $parts);
    }

    return $ns ? $ns . '\\' . $nameStr : $nameStr;
}

function isBuiltin(string $name) : bool
{
    return in_array(strtolower($name), ['self', 'static', 'parent', 'true', 'false', 'null', 'array', 'callable', 'int', 'float', 'string', 'bool', 'object', 'mixed', 'iterable', 'void', 'never']);
}

function addRef(string $fqn, string $file, string $ctx, int $line, array &$references, array $defined)
{
    $fqn = trim($fqn, '\\');
    if ($fqn === '' || isBuiltin($fqn)) return;
    if (strpos($fqn, 'Psr\\') === 0) return;
    if (isset($defined[$fqn])) return;
    if (strpos($fqn, '\\') === false) {
        $globals = ['arrayiterator', 'runtimeexception', 'invalidargumentexception', 'logicexception', 'exception', 'throwable', 'datetime', 'datetimeimmutable', 'dateinterval', 'closure', 'generator', 'arrayobject', 'splfileinfo', 'splfileobject', 'countable', 'iterator', 'iteratoraggregate', 'arrayaccess', 'serializable', 'jsonserializable', 'traversable', 'seekableiterator', 'recursiveiterator', 'pdostatement', 'pdoexception', 'reflectionclass', 'reflectionfunction', 'reflectionmethod', 'reflectionproperty', 'reflectionparameter', 'reflector', 'phpunit_framework_testcase', 'testcase'];
        if (in_array(strtolower($fqn), $globals)) return;
    }
    $references[$fqn][] = ['file' => $file, 'context' => $ctx, 'line' => $line];
}

function processType(?Node $type, string $ns, array $uses, string $file, string $ctx, int $line, array &$references, array $defined)
{
    if (! $type) return;
    if ($type instanceof Node\Name) {
        addRef(resolveName($type, $ns, $uses), $file, $ctx, $line, $references, $defined);
    } elseif ($type instanceof Node\UnionType || $type instanceof Node\IntersectionType) {
        foreach ($type->types as $t) {
            processType($t, $ns, $uses, $file, $ctx, $line, $references, $defined);
        }
    } elseif ($type instanceof Node\NullableType) {
        processType($type->type, $ns, $uses, $file, $ctx, $line, $references, $defined);
    }
}

class RefVisitor extends NodeVisitorAbstract
{
    private string $ns   = '';
    private array  $uses = [];
    private string $file;
    private array  $defined;
    private array  $references;

    public function __construct(string $file, array $defined, array &$references)
    {
        $this->file       = $file;
        $this->defined    = $defined;
        $this->references = &$references;
    }

    public function enterNode(Node $node) : ?int
    {
        if ($node instanceof Stmt\Namespace_) {
            $this->ns   = $node->name ? $node->name->toString() : '';
            $this->uses = [];
        } elseif ($node instanceof Stmt\Use_) {
            if ($node->type === Stmt\Use_::TYPE_NORMAL) {
                foreach ($node->uses as $use) {
                    $alias              = $use->getAlias()->toString();
                    $this->uses[$alias] = $use->name->toString();
                    $this->add($use->name->toString(), 'use-statement', $node->getStartLine());
                }
            }
        } elseif ($node instanceof Stmt\GroupUse) {
            if ($node->type === Stmt\Use_::TYPE_NORMAL) {
                $prefix = $node->prefix->toString();
                foreach ($node->uses as $use) {
                    $fqn                = $prefix . '\\' . $use->name->toString();
                    $alias              = $use->getAlias()->toString();
                    $this->uses[$alias] = $fqn;
                    $this->add($fqn, 'use-statement', $node->getStartLine());
                }
            }
        } elseif ($node instanceof Stmt\ClassLike) {
            // Implements (classes and enums)
            if (isset($node->implements)) {
                foreach ($node->implements as $impl) {
                    $this->add($this->resolve($impl), 'implements', $node->getStartLine());
                }
            }
            // Extends (classes and interfaces - in v5 both use 'extends')
            if (isset($node->extends) && $node->extends) {
                $ext = $node->extends;
                if ($ext instanceof Node\Name) {
                    $this->add($this->resolve($ext), 'extends', $node->getStartLine());
                } elseif (is_array($ext)) {
                    foreach ($ext as $e) {
                        if ($e instanceof Node\Name) {
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
        } elseif ($node instanceof Stmt\Function_ || $node instanceof Stmt\ClassMethod) {
            foreach ($node->params as $param) {
                $ctx = ($node instanceof Stmt\ClassMethod && $node->name->toString() === '__construct') ? 'constructor-param' : 'param-type';
                $this->processType($param->type, $ctx, $param->getStartLine());
                if ($param->default instanceof Expr\ClassConstFetch && $param->default->class instanceof Node\Name) {
                    $this->add($this->resolve($param->default->class), 'class-const-fetch', $param->default->getStartLine());
                }
                if ($param->default instanceof Expr\New_ && $param->default->class instanceof Node\Name) {
                    $this->add($this->resolve($param->default->class), 'new', $param->default->getStartLine());
                }
            }
            $this->processType($node->returnType, 'return-type', $node->getStartLine());
            foreach ($node->attrGroups as $ag) {
                foreach ($ag->attrs as $attr) {
                    $this->add($this->resolve($attr->name), 'attribute', $attr->getStartLine());
                }
            }
        } elseif ($node instanceof Stmt\Property) {
            foreach ($node->props as $prop) {
                $this->processType($node->type, 'property-type', $prop->getStartLine());
            }
            foreach ($node->attrGroups as $ag) {
                foreach ($ag->attrs as $attr) {
                    $this->add($this->resolve($attr->name), 'attribute', $attr->getStartLine());
                }
            }
        } elseif ($node instanceof Stmt\Catch_) {
            foreach ($node->types as $t) {
                $this->add($this->resolve($t), 'catch', $node->getStartLine());
            }
        } elseif ($node instanceof Expr\Instanceof_) {
            if ($node->class instanceof Node\Name) {
                $this->add($this->resolve($node->class), 'instanceof', $node->getStartLine());
            }
        } elseif ($node instanceof Expr\New_) {
            if ($node->class instanceof Node\Name) {
                $this->add($this->resolve($node->class), 'new', $node->getStartLine());
            }
        } elseif ($node instanceof Expr\StaticCall) {
            if ($node->class instanceof Node\Name) {
                $this->add($this->resolve($node->class), 'static-call', $node->getStartLine());
            }
        } elseif ($node instanceof Expr\ClassConstFetch) {
            if ($node->class instanceof Node\Name) {
                $this->add($this->resolve($node->class), 'class-const-fetch', $node->getStartLine());
            }
        } elseif ($node instanceof Stmt\Expression && $node->expr instanceof Expr\FuncCall && $node->expr->name instanceof Node\Name && $node->expr->name->toLowerString() === 'class_alias') {
            $args = $node->expr->getArgs();
            if (count($args) >= 2 && $args[0]->value instanceof Node\Scalar\String_) {
                $this->add($args[0]->value->value, 'class_alias target', $node->getStartLine());
            }
        }

        return null;
    }

    private function add(string $fqn, string $ctx, int $line)
    {
        addRef($fqn, $this->file, $ctx, $line, $this->references, $this->defined);
    }

    private function resolve(Node\Name $name) : string
    {
        return resolveName($name, $this->ns, $this->uses);
    }

    private function processType(?Node $type, string $ctx, int $line)
    {
        processType($type, $this->ns, $this->uses, $this->file, $ctx, $line, $this->references, $this->defined);
    }
}

echo "PASS 2: Parsing $total files for references...\n";

foreach ($phpFiles as $idx => $path) {
    if (($idx + 1) % 500 === 0) echo "  " . ($idx + 1) . " / $total\n";
    try {
        $stmts = $parser->parse(file_get_contents($path));
    } catch (Exception $e) {
        continue;
    }
    if (! $stmts) continue;
    $traverser = new NodeTraverser();
    $traverser->addVisitor(new RefVisitor($path, $defined, $references));
    $traverser->traverse($stmts);
}

// compat.php array-style aliases
$compatPath = $baseDir . '/components/compat.php';
if (file_exists($compatPath)) {
    $src = file_get_contents($compatPath);
    if (preg_match_all('/[\'"]([^\'"]+)[\'"]\s*=>\s*[\'"]([^\'"]+)[\'"]/', $src, $m)) {
        for ($k = 0; $k < count($m[1]); $k++) {
            $target = trim($m[2][$k], '\\');
            if (! isset($defined[$target])) {
                addRef($target, $compatPath, 'class_alias target', 0, $references, $defined);
            }
        }
    }
}

// Deduplicate
foreach ($references as $fqn => $refs) {
    $seen = [];
    $uniq = [];
    foreach ($refs as $r) {
        $k = $r['file'] . '|' . $r['context'] . '|' . $r['line'];
        if (! isset($seen[$k])) {
            $seen[$k] = true;
            $uniq[]   = $r;
        }
    }
    $references[$fqn] = $uniq;
}
ksort($references);

echo "\n=== BROKEN REFERENCES AUDIT REPORT ===\n\n";
$severityCounts = ['CRITICAL' => 0, 'MINOR' => 0];
foreach ($references as $fqn => $refs) {
    $isCritical = false;
    foreach ($refs as $r) {
        if (in_array($r['context'], ['extends', 'implements', 'class_alias target', 'catch', 'constructor-param', 'new'])) {
            $isCritical = true;
            break;
        }
    }
    $severity = $isCritical ? 'CRITICAL' : 'MINOR';
    $severityCounts[$severity]++;
    echo "MISSING: $fqn  [$severity]\n";
    foreach ($refs as $r) {
        echo "  - {$r['file']}:{$r['line']}  ({$r['context']})\n";
    }
    echo "\n";
}
echo "=== SUMMARY ===\n";
echo "Defined: " . count($defined) . "\n";
echo "Missing: " . count($references) . "\n";
echo "  CRITICAL: " . $severityCounts['CRITICAL'] . "\n";
echo "  MINOR: " . $severityCounts['MINOR'] . "\n";
