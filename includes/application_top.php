<?php
//  Entete pour les fichiers index pour l'initialiser le site  
//  ver 1.0 - 05/11/2020 - initial
//  ver 1.1 - 06/01/2021 - Gestion de l'Ajax

error_reporting(E_ALL & ~E_NOTICE);  // set the level of error reporting
require_once("config.php");
require_once(REP_INC . "class/DbSqlite.php");
require_once(REP_INC . "class/SiteCore.php");
require_once(REP_INC . "class/createBase.php");
require_once(REP_INC . "fonctUtile.php");
require_once(REP_INC . "class/Apphtml.php");

// class du site
class Mysite extends SiteCore {
  var $siteMessage      = "";
  var $siteMessagePopUp = "";
  var $siteMessageLevel = "";
  var $menuSel = "";
  var $appelAjax;

  function majMenu () {
    // Mis a jour le menu à partir 
    $this->menuSel = utilProtected($_GET["menu"]);
  }

  function majFormMail () {
    // Gestion d'envoi d'un mail 
    $form = $_GET["form"] ?? "";
    if (htmlentities($form)  !== "mail")
      return;

    // on lit les infos pour envoyer le contact
    if ( ! ($tableMail = $this->litInfo("contact")) ) {
      $this->siteMessagePopUp = MSG_MAIL_KO;
      return;
    }

    $mailMsg = "";
    foreach ($_POST as $key => $val) {
      $mailMsg  .= "<strong> $key : </strong> " . htmlentities($val) . "<br>\n";
    }
    // $this->siteMessage = "<pre>$msgMail</pre>";
    $mailok = utilSendMail($tableMail["contenu"], $tableMail["meta"], $tableMail["titre"],  $mailMsg);
    $this->siteMessagePopUp =  ($mailok) ? MSG_MAIL_OK : MSG_MAIL_KO;
  }
  function majAjax() {
    // Gestion d'envoi d'un mail 
    $ajax = utilProtected($_GET["ajax"]);

    if ($ajax == "1") 
      $this->appelAjax=true;
    else 
      $this->appelAjax=false;
  }
}

$siteRoot = utilRepRacine((string) $_SERVER ["DOCUMENT_ROOT"], (string) $_SERVER ["REQUEST_URI"]);
$siteLang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);

// on verifie la présence du fichier DB
try {
  if ((TYPE_BASE === "SQLITE")) {
    // if ($linkDB->tableExiste("info"))  {
    $siteCnf = new Mysite(new DbSqlite(BASE_DB), PREFIXE_DB, $siteLang);
  }
} catch  (Exception $e) {
  die ("<h1> ERREUR GRAVE : problème d'accès à la base de donnée </h1> <br> <p>" . $e->getMessage() . " </p>");
}

$siteCnf->majMenu();
$siteCnf->majFormMail();  // on gere les mails
$siteCnf->majAjax();