<?php
/* 
Fonctions pour lire les données au format XML

MFE le 4/10/2020


=> necessite le module simplexml
=> sudo apt install php7.4-xml
*/

// lit les données site au format xml
function load_site_xml (string $fic ){

//	$fic =  dirname (__DIR__) . DIRECTORY_SEPARATOR . "xmldata" . DIRECTORY_SEPARATOR . $fichier ;
	
	if ( ! file_exists($fic) ) {
		// echo "<strong> Error: fichier $fichier n'existe pas </strong>" ;
		return false ;
	}
	// file_exists($fic)  or die ( "<strong> Error: fichier $fic n'existe pas </strong>" ) ;
	
	$xml=simplexml_load_file($fic) or die("Error: Cannot create object");
	return $xml;

}

// affiche le menu pour une langue donnée
//manque la gestion du liens 
function print_menu_xml (object $xml, string $lang, int $id_menu_actif, string $url, bool $url_rw = false ): string {
	
	$html_ret= "";
	foreach($xml->children() as $fils) {
		if ( $fils->langue == $lang )  {
			if ( $url_rw )  { // re ecriture d'adresse
				$href =  $url . rawurlencode (  (string) $fils->titre ) . '/'  ;
				// $href =  './index_2.php/' . rawurlencode ( (string) $fils->titre  . '/' ) ;
			} else {
				$href = $url . "?" . http_build_query ( [ 'id' => (string) $fils->id ] ) ;	
			}
			$active = ($id_menu_actif == (int)$fils->id )  ?  'class="active"': ''  ;
			$html_ret .=  "<li $active > <a href='$href'> " . $fils->libelle . "</a> </li> " ;
		}
	} 
	return $html_ret;
}

// Affiche un menu pour un site ayant tout le contenu dans la meme page
//  manque la gestion du liens 
function print_menu_xml_one (object $xml, string $lang, string $tagstart, string $tagend,  string $active, string $notactive ): string {
	
	$html_ret= "";
	foreach($xml->children() as $fils) {
		if ( $fils->langue == $lang )  {
			$href = "#{$fils->id}";	

			$html_ret .=  "{$tagstart} href='$href' > " . $fils->libelle . " $tagend " ;
		}
	} 
	return $html_ret;
}


/* 
<button class="w3-button w3-black">ALL</button>
<button class="w3-button w3-white"><i class="fa fa-diamond w3-margin-right"></i>Design</button>
<button class="w3-button w3-white w3-hide-small"><i class="fa fa-photo w3-margin-right"></i>Photos</button>
<button class="w3-button w3-white w3-hide-small"><i class="fa fa-map-pin w3-margin-right"></i>Art</button>
 */


/*  --------------------------------------------------------------------------------------------
Retourne  l'objet sur la page courante à afficher

si le parametre page_id vaut  
=>  -1  on prendre la page par defaut de la langue
=>   0   recherche par le titre de la page
=> > 0  on  prend l'id de la page le prochain ID  
 */

 // rend la page courante 
function page_courante (object $xml, int $page_id, string $lang = ""  , string $url_rw = "")  {
	// echo "*deb* $page_id $url_rw $lang <br>" ;
	// on recherche la page par defaut
	if ($page_id == -1) {
		foreach($xml->children() as $fils) {
			if ( $fils->defaut == "oui" )  {
				if ( $lang == ""  || $lang == (string) $fils->langue ) {
					return $fils ;		
				}
			}
		}
	} elseif ($page_id == 0 )  { // on recheche par titre
		foreach($xml->children() as $fils) {
			$lpage =  rawurlencode (  (string) $fils->titre  ) ;
			// echo " -> $url_rw  <br> <-" . rawurlencode ( (string) htmlentities( $fils->titre) ) . "<br>" ;
			if (  $lpage == $url_rw )  {
				return $fils ;
			}
		}
	} else {
		foreach($xml->children() as $fils) {
			if ( (int) $fils->id >= $page_id )  {
				if ( $lang == ""  || $lang == (string) $fils->langue ) {
					return $fils ;		
				}
			}
		}
	} 

	// echo " ** deb ** page pas trouve  <br> " ;
	return false ;
}


?>
