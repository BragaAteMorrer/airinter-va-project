<?php
// Recover only dependency files from the user's matching backup, never its .env/caches.
if (!is_file('vendor/autoload.php')) {
    $zip = new ZipArchive();
    if ($zip->open('backup_after_import.zip') !== true) {
        throw new RuntimeException('Missing dependencies: run composer install or supply the original backup.');
    }
    for ($i = 0; $i < $zip->numFiles; $i++) {
        $name = $zip->getNameIndex($i);
        if (str_starts_with($name, 'vendor/') && !str_contains($name, '..') && !str_ends_with($name, '/')) {
            @mkdir(dirname($name), 0755, true);
            file_put_contents($name, $zip->getFromIndex($i));
        }
    }
    $zip->close();
}
if (is_file('backup_after_import.zip')) unlink('backup_after_import.zip');
