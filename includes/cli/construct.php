<?php

$repDonnees = dirname(__DIR__) . DIRECTORY_SEPARATOR . "donnees" . DIRECTORY_SEPARATOR;
$phpDonnees = dirname(__DIR__) . DIRECTORY_SEPARATOR . "DbClass.php";

$tabPhp = [];
$phpHeader=<<< EOF
<?php
// Module pour inclure les tables du projet 

EOF;
$codeRequire = 'require_once(REP_INC . "donnees/%s");';


// liste tous les fichiers contenant les tables php
if (is_dir($repDonnees)) {
    if ($dh = opendir($repDonnees)) {
        while (($file = readdir($dh)) !== false) {
            if ( substr($file, -3) === "php") {
                $fileClass = file_get_contents("{$repDonnees}/{$file}");
                preg_match_all("|(.*)=.*new DbTable|U", $fileClass, $class, PREG_PATTERN_ORDER);
                echo  $class[1][0] . "\n";

                $tabPhp[$file] = $class[1][0];
            }
        }
        closedir($dh);
    }
}

// on produit le fichier pour les inclustions
echo $phpHeader;
foreach ($tabPhp as $file => $class) {
    printf($codeRequire . "\n",  $file);
}
echo "\n";

