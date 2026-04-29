<?php
declare(strict_types=1);

namespace Avax\Components\HTTP\Request\System\Capabilities\Files;

final class NormalizeUploadedFiles {
    public function normalize(array $f): array {
        $norm = [];
        foreach ($f as $k => $v) {
            if ($v instanceof UploadedFile) $norm[$k] = $v;
            elseif (isset($v['tmp_name'])) $norm[$k] = new UploadedFile($v['tmp_name'], $v['size'] ?? null, $v['error'], $v['name'] ?? null, $v['type'] ?? null);
        }
        return $norm;
    }
}
