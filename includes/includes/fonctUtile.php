<?php 
//    Collection de fonctions utiles 
//    4/10/2020 - ajout d'une fonction pour calculer la repertoire racine du site 
//    26/12/2020 - ajout de la fonction utilProtect

function ecritFichier(string $ligne, string $fichier): int {
// ecrit dans un fichier et si ce parametre est null , il l'affiche sur l'ecran
  $ligne .= "\n";
  if ($fichier != "") {
    return file_put_contents($fichier, $ligne, FILE_APPEND);
  }
  else {
    echo $ligne;
    return 0;
  }
}

function utilGetSite(array $get, string $parametre): string {
//  lit la variable  GET du site
  if ( !empty($get[$parametre])) {
    utilDebug("$parametre =>", $get[$parametre], false);
    return (string)htmlentities($get[$parametre]);
  }
  return "";
}

function utilSendMail(string $mail_to, string $mail_from,  string $sujet, string $message): bool {
// send email contat
  $headers  = "MIME-Version: 1.0 \n";
  $headers  .= "From: $mail_from \nContent-Type: text/html; charset='iso-8859-1'\n"; 

/*   utilDebug("mail to ",  $mail_to, true);
  utilDebug("mail from ",  $mail_from, true);
  utilDebug("sujet ",  $sujet, true);
  utilDebug("message",  $message, true); */
 	return (bool)mail($mail_to, $sujet, $message, $headers);
} 

function utilRepRacine(string $root , string $uri): string {
// retourne le reperoire Racine du site 

    $loc_racine =  $root;
    $loc_tmp = explode('/', $uri);

    foreach ( $loc_tmp as $loc_item) {
        // echo "** deb-fct-racine  ** racine=$loc_racine item=$loc_item <br>";
      if ( empty  ($loc_item) ) {
        $loc_racine .= DIRECTORY_SEPARATOR ;
      } elseif ( strstr ( $loc_item, "." ) || strstr ( $loc_item, "?") )  {
        break;  // on sort de la boucle car la valeur est un fichier , elle possede une extention "." 
      } else {
        $loc_racine .= $loc_item ;
      }
    }
    return $loc_racine;
}

function utilDebug(string $libelle, $obj, bool $trace = true): void {
// affiche l'objet PHP
  if ($trace) {
    echo "<br><strong>$libelle</strong><pre>";
    print_r($obj);
    echo "</pre>";
  }
}

function utilProtected($strUnSecure = null): string {
// Protege une variable saisie par l'utilisateur
  $protected =  str_replace([';', ',', '\\', '&', '\'', '"'], "", $strUnSecure);  // supprime les caracteres problematiques
  return htmlentities($protected);
}