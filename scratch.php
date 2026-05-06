<?php

use PhpParser\Node\Name;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\ParserFactory;

require 'vendor/autoload.php';
$parser = (new ParserFactory())->createForNewestSupportedVersion();
$content = file_get_contents('components/DataStack/Data/System/Capabilities/Collections/Arrhae.php');
$stmts = $parser->parse($content);
$ns = '';
foreach ($stmts as $stmt) {
    if ($stmt instanceof Namespace_) {
        $ns = $stmt->name instanceof Name ? $stmt->name->toString() : '';
        foreach ($stmt->stmts as $inner) {
            if ($inner instanceof ClassLike && isset($inner->name)) {
                $fqn = $ns !== '' ? $ns.'\\'.$inner->name->toString() : $inner->name->toString();
                echo "DEFINED: $fqn\n";
            }
        }
    }
}
