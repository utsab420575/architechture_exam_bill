<?php
/**
 * Fix file and folder permissions in a Laravel project
 * Usage: php fix-permissions.php
 */

$basePath = __DIR__; // Laravel root

// Permissions
$dirPerm  = 0755;
$filePerm = 0644;

// Writable directories (Laravel specific)
$writableDirs = [
    "$basePath/storage",
    "$basePath/bootstrap/cache",
];

function chmodRecursive($path, $dirPerm, $filePerm) {
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );

    foreach ($iterator as $item) {
        if ($item->isDir()) {
            chmod($item->getPathname(), $dirPerm);
        } else {
            chmod($item->getPathname(), $filePerm);
        }
    }
}

// 1. Fix all project files/folders
echo "Updating file/folder permissions...\n";
chmodRecursive($basePath, $dirPerm, $filePerm);

// 2. Make Laravel writable dirs 775
foreach ($writableDirs as $dir) {
    if (is_dir($dir)) {
        chmodRecursive($dir, 0775, 0664);
        chmod($dir, 0775);
    }
}

echo "✅ Permissions updated successfully!\n";
