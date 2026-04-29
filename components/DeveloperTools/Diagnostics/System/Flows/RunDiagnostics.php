<?php
namespace Avax\Components\DeveloperTools\Diagnostics\System\Flows;
final class RunDiagnostics { public static function execute(): array { return [\Avax\Components\DeveloperTools\Diagnostics\System\PublicSurface\Diagnostics::health()]; } }
