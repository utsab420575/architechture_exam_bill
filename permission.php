<?php
/**
 * Laravel Standard Permission Fixer
 *
 * Folder Permission:
 *      755
 *
 * File Permission:
 *      644
 *
 * Writable Laravel Folders:
 *      storage          775
 *      bootstrap/cache  775
 *
 * Usage:
 *      php permission.php
 */


error_reporting(E_ALL);
ini_set('display_errors', 1);


// Laravel root
$basePath = __DIR__;


// Standard permissions
$folderPermission = 0755;
$filePermission   = 0644;


// Laravel writable directories
$writableFolders = [
    $basePath . '/storage',
    $basePath . '/bootstrap/cache'
];


// Skip folders
/*$excludedFolders = [
    '.git',
    '.idea',
    'node_modules'
];*/
$excludedFolders = [];


/**
 * Check excluded folder
 */
function isExcluded($path, $excludedFolders)
{
    foreach ($excludedFolders as $folder) {

        if (
            strpos(
                $path,
                DIRECTORY_SEPARATOR . $folder . DIRECTORY_SEPARATOR
            ) !== false
        ) {
            return true;
        }


        if (basename($path) == $folder) {
            return true;
        }
    }


    return false;
}



/**
 * Change permission recursively
 */
function setPermissionRecursive(
    $path,
    $folderPermission,
    $filePermission,
    $excludedFolders
) {


    if (isExcluded($path, $excludedFolders)) {
        return;
    }


    if (is_dir($path)) {


        if (chmod($path, $folderPermission)) {

            echo "Folder: 755  $path\n";

        } else {

            echo "FAILED Folder: $path\n";

        }



        $items = scandir($path);


        foreach ($items as $item) {


            if ($item == '.' || $item == '..') {
                continue;
            }


            setPermissionRecursive(

                $path . DIRECTORY_SEPARATOR . $item,

                $folderPermission,

                $filePermission,

                $excludedFolders

            );

        }



    } else {


        if (chmod($path, $filePermission)) {

            echo "File: 644    $path\n";

        } else {

            echo "FAILED File: $path\n";

        }

    }

}




echo "====================================\n";
echo " Laravel Permission Fix Started\n";
echo "====================================\n\n";



// Fix Laravel root folder

echo "Setting Laravel root permission...\n";

chmod($basePath, 0755);



echo "\nUpdating files and folders...\n\n";


// Apply normal permissions

setPermissionRecursive(

    $basePath,

    $folderPermission,

    $filePermission,

    $excludedFolders

);





// Fix writable folders

echo "\nSetting Laravel writable folders...\n\n";


foreach ($writableFolders as $folder) {


    if (is_dir($folder)) {


        echo "Writable folder: $folder\n";


        setPermissionRecursive(

            $folder,

            0775,

            0664,

            []

        );


        chmod($folder,0775);

    }

}



echo "\n====================================\n";
echo " Permission Update Completed\n";
echo "====================================\n";


?>