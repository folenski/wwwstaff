<?php
/**
 * Connexion à la base de données du site 
 *
 * @author  Mario Ferraz
 * @since 1.0  - 13/01/2021 - initiale
 *
 */

// on verifie la présence du fichier DB
try {
  if ((TYPE_BASE === "SQLITE")) {
    // if ($linkDB->tableExiste("info"))  {
    $siteDB = new DbSqlite(BASE_DB);
  }
} catch  (Exception $e) {
  http_response_code(500);  // on renvoit une erreur serveur
  die ("<h1> ERREUR GRAVE : problème d'accès à la base de donnée </h1> <br> <p>" . $e->getMessage() . " </p>");
}