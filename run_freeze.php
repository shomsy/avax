<?php
$output = shell_exec('php tooling/refactor/freeze-component-taxonomy.php 2>&1');
file_put_contents('freeze_dry_run.txt', $output);
echo "Done\n";
