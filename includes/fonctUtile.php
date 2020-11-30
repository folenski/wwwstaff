<?php 
/*  
    Collection de foncitons utiles 
    le 4/10/2020  ajout d'une foncton pour calculer la repertoire racine du site 
*/

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
	$headers  = "From: '$mail_from' \n Content-Type: text/html; charset='UTF-8' \n" .
	            "Content-Transfer-Encoding: 8bit \n" ; 
 	return (bool)mail($mail_to, $sujet, $message, $headers) ;
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

function utilDebug(string $libelle, $obj, bool $trace = false) {
// affiche l'objet PHP
  if ($trace) {
    echo "<br><strong>$libelle</strong><pre>";
    print_r($obj);
    echo "</pre>";
  }
}