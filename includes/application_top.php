<?php
/*
  staff_kiev.com 
  fonction d'initialisation du site staff_kiev
*/
// set the level of error reporting
error_reporting(E_ALL & ~E_NOTICE);

require_once('includes/fonctions_xml.php') ;
require_once('includes/utile.php');

$site_uri  = $_SERVER ["REQUEST_URI"] ;
$site_root = $_SERVER ["DOCUMENT_ROOT"] ;
$site_xml  = $site_root . DIRECTORY_SEPARATOR . "xmldata" . DIRECTORY_SEPARATOR ;

// lecteur du fichier de conf
if ( ! $sitecnf=load_site_xml ($site_xml . "site.xml") ) {
   // site en maintenance
  require "under.html" ;
  //  echo  '<script> document.location.href="under.html"; </script>';
   exit;
}

// lecture du fichier contenant les pages du site
if ( ! $data=load_site_xml ( $site_xml . $sitecnf->contenu) ) {
  require "under.html" ; // site en maintenance 
  exit;
}

// Gestion de l'entete url 
// cas du mail 
$l_action  = false ;
if (  ! empty ($_GET ['action']) ) {
    if ( htmlentities( $_GET ['action' ] ) == 'mail')  {
      if ( $sitecnf->deb == "oui" ) {
        echo "** deb **  On traite l'envoi des mails <br>" ;
        print_r ( $_POST);
        echo "<br>";
      }
      $ret = (bool) send_mail( $sitecnf->mail_to, $sitecnf->mail_from, $sitecnf->titre . " contact",  $_POST['cont_form_name'],  $_POST['cont_form_tel'], $_POST['cont_form_email'], $_POST['cont_form_msg']) ;
      if (  $ret )  {
        $message = ["w3-blue", "<strong> Mail sent ! </strong> Thank you."]; 
      } else {
        $message = ["w3-red", "<strong> Error ! </strong> Mail no sent, please try later."];  
      }
      $l_action = true ; 
    } 
} 

$id_page = ( ! empty ( $_GET ["id"]) ) ? (int) $_GET ["id"] : -1 ;  

// on determine la page la page courant 
if ( $sitecnf->url_rw == "oui" && !  $l_action  ) {
 
  $urldata =  explode("/", $_SERVER ["REQUEST_URI"] );

  $id_page = ( empty ( $urldata[2] ) ) ? -1 : 0 ;

  if ( ! $site_page_act = page_courante ($data, $id_page, $sitecnf->langue, (string) $urldata[2] ) ) {
    require "notfound.html" ; // site en maintenance 
    exit;
  }
  if ( $sitecnf->deb == "oui" ) {
    echo " ** deb **  Page trouvé <br> " ;
  }
} else {
  if (  !  $site_page_act = page_courante ($data, $id_page, $sitecnf->langue) ) {
    require "notfound.html" ; // site en maintenance 
    exit;
  }
}

$site_id_page = (int)$site_page_act->id ; // on reprend l'id de la page active
$site_url_site = $sitecnf->url . $urldata[1] . "/" ;
$site_url_rw = (bool) ($sitecnf->url_rw == "oui") ;

if ( $sitecnf->deb == "oui" ) {
  echo " ** deb **  id_page : $site_id_page  -  url : $site_url_site  <br> " ;
}
?>