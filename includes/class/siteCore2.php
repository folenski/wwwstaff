<?php
// Class pour manipuler les objets menus, pages et article d'un site
// v1.0  - 07/11/2020 - mfe

include_once(REP_INC . "classCreateBase.php");
include_once(REP_INC . "fonctUtile.php"); 

class siteCore {
    var $titre  = "";
    var $idMenu = -1;

    private $_driverDB;
    private $_prefixe;

    function __construct($driverDB, string $lang = 'fr') {
    // Constructeur de l'objet, Paramatre 
    // le fichier base de données sqlite, le prefixe des tables, la langue du site

        $this->_driverDB = $driverDB;
        $this->_prefixe = $driverDB->prefixe;

         // on recherche via la langue
        $req = "SELECT i.idGrpDown, i.titre, I.contenu 
                FROM {$this->_prefixe}categorie c, {$this->_prefixe}grpinfo g, {$this->_prefixe}info i
                WHERE c.idCat = g.idCat  AND g.idInfo = i.idInfo  AND i.actif = 'oui'
                  AND i.meta = 'lang=$lang'
                  AND c.nom = 'site'
                ORDER BY nbrClassement  ";
        $ret = ($this->_driverDB)->selectSQL($req);
        if ($ret->erreur)  return;  // on sort si il y une erreur
        if (count($ret) > 0) {
            $this->titre   = $ret['titre'];
            $this->idMenu  = (int)$ret['idGrpDown'];
            return;
        }

        // si la recherche par via langue ne fonctionne pas, on prendre la 1ere page
        $req = "SELECT i.idGrpDown, i.titre, I.contenu 
                FROM {$this->_prefixe}categorie c, {$this->_prefixe}grpinfo g, {$this->_prefixe}info i
                WHERE c.idCat = g.idCat  AND g.idInfo = i.idInfo  AND i.actif = 'oui'
                  AND c.nom = 'site'
                ORDER BY nbrClassement";
        $ret = ($this->_driverDB)->selectSQL($req);
        if ($ret->erreur)  return;  // on sort si il y une erreur
        if (count($ret) > 0) {
            $this->titre   = $ret['titre'];
            $this->idMenu  = (int)$ret['idGrpDown'];
            return;
        }
    }

    function fin() {
    // fermeture de la base 
        ($this->_driverDB)->fin();
        unset($this->_driverDB);
    }

    function openCurInfo(string $nomCat, string $idGrp="", string $meta="") {
    // ouverture un curseur
        if ($nomCat !== "") 
            $whereCat = "AND c.nom = '$nomCat'";

        if ($meta !== "")
            $whereInfo = "AND i.meta = '$meta'";
        elseif ($idGrp !== "")
            $whereGrp = "AND g.idGrpItem = $idGrp";


        $req = "SELECT i.*  
                FROM {$this->_prefixe}categorie c, {$this->_prefixe}grpinfo g, {$this->_prefixe}info i 
                WHERE c.idCat = g.idCat AND g.idInfo = i.idInfo  AND i.actif = 'oui'
                      $whereGrp $whereCat $whereInfo
                ORDER BY nbrClassement ";

        utilDebug("req (def) =>", $req, false);
        return ($this->_driverDB)->openCur($req);
    }

    function openCurTable(string $table) {
    // ouverture un curseur sur la table donnée en paramétre
        return ($this->_driverDB)->openCur("SELECT * FROM {$this->_prefixe}$table");
    }

    function afficheInfo($curseur, string $format, ...$champs): int {
    // Affiche les infos, retourne le nbr d'occurence
        $nbr=0;
        while ($info = ($this->_driverDB)->fetchCur($curseur)) {
            $tab = array();
            foreach ($champs as $valeur) {
                $tab[] = $info[$valeur];
            }
            printf($format, ...$tab);
            utilDebug("info  =>", $info, false);
            $nbr++;
        }
        ($this->_driverDB)->closeCur();

        return $nbr;
    }

    function nbrInfo(string $nomCat, string $idGrp="", string $meta=""): int {
    // Comptage des elements
        if ($nomCat !== "") 
            $whereCat = "AND c.nom = '$nomCat'";

        if ($meta !== "")
            $whereInfo = "AND i.meta = '$meta'";
        elseif ($idGrp !== "")
            $whereGrp = "AND g.idGrpItem = $idGrp";


        $req = "SELECT count (i.idInfo) AS 'nbr' 
                FROM {$this->_prefixe}categorie c, {$this->_prefixe}grpinfo g, {$this->_prefixe}info i 
                WHERE c.idCat = g.idCat AND g.idInfo = i.idInfo  AND i.actif = 'oui'
                        $whereGrp $whereCat $whereInfo
                ORDER BY nbrClassement ";

        utilDebug("req (def) =>", $req, true);
        $info = ($this->_driverDB)->selectSQL($req);
        if ( ($this->_driverDB)->erreur ) 
            return 0;

        utilDebug("ret req  =>", $info, true);
        return  $info["nbr"];
    }

 }