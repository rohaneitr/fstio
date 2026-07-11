<?php

$dirs = [
    'app/Application',
    'app/Domain',
    'app/Infrastructure',
    'app/Foundation',
    'app/Http',
    'app/Providers',
    'app/Listeners',
];
foreach ($dirs as $dir) {
    if (is_dir($dir)) {
        $count = iterator_count(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS)));
        echo "$dir: $count files\n";
    } else {
        echo "$dir: Not found\n";
    }
}
