<?php
/**
 * Gestion des appels AJAX en REST 
 *
 * @author  Mario Ferraz
 * @since 1.0  13/01/2021 
 * @version 1.0.0 version initiale
 *
 */

$ApiStaff = array();

$www = dirname(__DIR__) . DIRECTORY_SEPARATOR;
require_once($www . "config.php");
require_once(REP_INC . "inc_modules.php");
require_once(REP_INC . "connexionDB.php");  // connexion à la base de données du site
require_once(REP_INC . "inc_tables.php");
require_once(REP_INC . "inc_apiREST.php");

// Headers requis
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, GET");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// On vérifie que la méthode utilisée est correcte

$methode = $_SERVER['REQUEST_METHOD'];
$service = $_SERVER["PATH_INFO"] ??  "";

if ($service === "") {
    http_response_code(401);
    echo json_encode(["message" => "Le service n'est pas présent" ]);
    exit;    
}

$service = substr(htmlentities($service), 1);
if (! array_key_exists($service, $ApiStaff)) {
    http_response_code(401);
    echo json_encode(["message" => "Le service {$service} n'est pas valide" ]);
    exit;
}

// on recherche la class
$MethClass = $ApiStaff[$service];

if ( ! isset ($MethClass) ) {
    http_response_code(501);
    echo json_encode(["message" => "Problème technique sur le service {$service}" ]);    
}

if (! $MethClass->estAutorise($service, $methode)) {
    http_response_code(401);
    echo json_encode(["message" => "La methode {$methode} n'est pas autorisé pour le service {$service}" ]);
    exit;
}

switch ($methode) {
 
    case "POST":
        $donnees = json_decode(file_get_contents("php://input"));

        if ($donnees === null ) {
            http_response_code(406);
            echo json_encode(["message" => "Erreur données au format json non trouvées"]);  
            exit;
        }

        $msg = $MethClass->maj($siteDB, $donnees); 
        http_response_code($msg[0]["http"]);
        echo json_encode($msg[1]); 
        break;

    default :
        http_response_code(405);
        echo json_encode(["message" => "La méthode n'est pas autorisée"]);
}
