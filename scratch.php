<?php
require 'vendor/autoload.php';
$parser = (new PhpParser\ParserFactory())->createForNewestSupportedVersion();
$content = file_get_contents('components/DataStack/Data/System/Capabilities/Collections/Arrhae.php');
$stmts = $parser->parse($content);
$ns = '';
foreach ($stmts as $stmt) {
    if ($stmt instanceof PhpParser\Node\Stmt\Namespace_) {
        $ns = $stmt->name instanceof PhpParser\Node\Name ? $stmt->name->toString() : '';
        foreach ($stmt->stmts as $inner) {
            if ($inner instanceof PhpParser\Node\Stmt\ClassLike && isset($inner->name)) {
                $fqn = $ns !== '' ? $ns . '\\' . $inner->name->toString() : $inner->name->toString();
                echo "DEFINED: $fqn\n";
            }
        }
    }
}
