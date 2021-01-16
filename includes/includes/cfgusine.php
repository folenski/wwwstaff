<?php
/**
 * **cfgusine.php*** 
 * Variables nécessaires pour tous les sites
 * 
 * @author  Mario Ferraz
 * @since 1.0  15/01/2021 
 * @version 1.0.0 Version initiale
 *
 */

// Déclaration des repertoires
define("REP_ADM", SITE_ROOT .  "admin"     . DIRECTORY_SEPARATOR);
define("REP_INC", SITE_ROOT . "includes"  . DIRECTORY_SEPARATOR);

define("REP_IMG", SITE_URL . "images"   . DIRECTORY_SEPARATOR);
define("REP_MED", SITE_URL . "media"    . DIRECTORY_SEPARATOR);
define("REP_CSS", SITE_URL . "css"      . DIRECTORY_SEPARATOR);
define("REP_JS",  SITE_URL . "js"       . DIRECTORY_SEPARATOR);


//  Pour SQLITE
if (  TYPE_BASE  === "SQLITE" ) {
    define("REP_DB",  SITE_ROOT . "sqldb"     . DIRECTORY_SEPARATOR);
    define("BASE_DB", REP_DB . SQLITE_NAME );
}

define("MSG_ADM", array (
     'Succès'                      
    ,'Erreur d\'accès base'
    ,'Le module d\'administration a été désactivé'
    ,'Ce mail existe déjà'
    ,'Le mot de passe doit être supérieur ou égale à 8 caractères'
    ,'Utilisateur ou mot de passe erronés'  //5
    ,'Vous avez été Déconnecté'  //6

));
