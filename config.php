<?php
// Constantes pour la configuration pour le site
// v1.1 - 01/11/2020 - MFE

//define("SITE_URL", "http://localhost:8080/");
define("SITE_URL", "");
define("FW_LOCAL",  true);  // si on souhaite utiliser les frameworks en local

// Déclaration des repertoires
define("REP_CSS", SITE_URL .  "css"       . DIRECTORY_SEPARATOR);
define("REP_JS",  SITE_URL .  "js"        . DIRECTORY_SEPARATOR);
define("REP_DB",  SITE_URL .  "sqldb"     . DIRECTORY_SEPARATOR);
define("REP_IMG", SITE_URL .  "images"    . DIRECTORY_SEPARATOR);
define("REP_MED", SITE_URL .  "media"     . DIRECTORY_SEPARATOR);
define("REP_INC", SITE_URL .  "includes"  . DIRECTORY_SEPARATOR);

//  Pour SQLITE
define("TYPE_BASE", "SQLITE");
define("BASE_DB", REP_DB . "staffKiev.db" );
define("PREFIXE_DB", "site");                   // prefixe pour les tables