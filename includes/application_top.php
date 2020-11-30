<?php
/*
  fichier a appeler pour l'initialiser du site  
  fonctionnalités : ajout du support sqlite3
  ver 1.3  05/11/2020
*/
require_once("config.php");
require_once(REP_INC . "class/baseSqlite.php");
require_once(REP_INC . "class/siteCore2.php");
require_once(REP_INC . "fonctUtile.php");
require_once(REP_INC . "fonctHtml.php");
error_reporting(E_ALL & ~E_NOTICE);  // set the level of error reporting

// class du site
class mySite extends siteCore {
  var $siteMessage      = "";
  var $siteMessagePopUp = "";
  var $menuSel = "";

  function majMenu () {
    // Mis a jour le menu à partir 
    $this->menuSel = htmlentities($_GET["menu"]) ?? "";
  }

  function majFormMail () {
    // Gestion d'envoi d'un mail 
    if (htmlentities($_GET["form"])  !== "mail")
      return;

    foreach ($_POST as $key => $val) {
      $msgMail  .= "<strong> $key : </strong> " . htmlentities($val) . "<br>\n";
    }
//    $this->siteMessage = "<pre>$msgMail</pre>";
    $mailok = utilSendMail ("mario.ferraz@yahoo.fr", "noreply@staff-kiev.com", "contact {$siteCnf->titre}",  $msgMail);
    $this->siteMessagePopUp =  ($mailok) ? MSG_MAIL_OK : MSG_MAIL_KO;
  }
}

$siteRoot = utilRepRacine((string) $_SERVER ["DOCUMENT_ROOT"], (string) $_SERVER ["REQUEST_URI"]);
$siteLang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);

// on verifie la présence du fichier DB
if ((TYPE_BASE === "SQLITE")) {
  // if ($linkDB->tableExiste("info"))  {
  $siteCnf = new mySite(new driverSqlite(BASE_DB, PREFIXE_DB), $siteLang);
}
else {
  echo "<h1> ERREUR GRAVE : problème d'accès à la base de donnée </h1>";
  exit;
}


//$siteMenuCourant = utilGetSite($_GET, "menu");
$siteAdm = utilGetSite($_GET, "adm");

$siteCnf->majMenu();
$siteCnf->majFormMail();  // on gere les mails
