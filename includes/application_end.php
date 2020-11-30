<?php
/*
  fichier a appeler pour terminer   
  fonctionnalités : ajout du support sqlite3
  ver 1.0 - 05/11/2020  - MFE
*/
error_reporting(E_ALL & ~E_NOTICE);  // set the level of error reporting
require_once(REP_INC . "classSite.php");

$siteCnf->fin();
unset($siteCnf);