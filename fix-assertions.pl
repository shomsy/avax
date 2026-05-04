<?php
$file = $argv[1] ?? '';
if (!$file || !file_exists($file)) exit(1);

$content = file_get_contents($file);
$lines = explode("\n", $content);
$fixed = false;

foreach ($lines as $i => $line) {
    // Наћи assert* методе које се завршавају са ; уместо )
    // Пример: $this->assertFalse($var; -> $this->assertFalse($var));
    // Али не треба да утинасе исправне: $this->assertFalse($var));
    
    // Проверавамо да ли линија има assertMethod( без затворене заглавља
    if (preg_match('/(\$this->assert[A-Za-z]+\([^)]+)\);/', $line, $m) && 
        !preg_match('/(\$this->assert[A-Za-z]+\([^)]+)\)\);/', $line)) {
        $lines[$i] = preg_replace('/(\$this->assert[A-Za-z]+\([^)]+)\);/', '$1));', $line);
        $fixed = true;
    }
}

if ($fixed) {
    file_put_contents($file, implode("\n", $lines));
    echo "Fixed: $file\n";
}