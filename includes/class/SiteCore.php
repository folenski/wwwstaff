<?php
// Class pour manipuler les objets menus, pages et article d'un site
// v1.0  - 07/11/2020 - mfe
// v1.1  - 22/12/2020 - ajout de litinfo pour les contacts
// v1.2  - 23/12/2020  - Creation des tables si la base est vide 

class SiteCore {
    const MODULEVS_SITECORE="1.20";
    public $titre  = "";
    var $idMenu = -1;

    private $_driverDB;
    private $_prefixe;

    function __construct(object $driverDB, string $prefix, string $lang = 'fr') {
    // Constructeur de l'objet, Paramatre 
    // le fichier base de données sqlite, le prefixe des tables, la langue du site

        $this->_driverDB = $driverDB;
        $this->_prefixe = $prefix;
        if (substr($this->_prefixe, -1) != "_" )   // on rajoute l'underscore si le prefixe n'en contient pas
            $this->_prefixe .= "_";

//        $this->privInitBase();  // on créé les tables en cas de besoin

        if ($this->privInitindex($lang))
            return;

        $this->privInitindex(); // on recherche la page par defaut
    }

    public function fin() {
    // fermeture de la base 
        ($this->_driverDB)->fin();
        unset($this->_driverDB);
    }

    public static function version(): string {
    // retourne la version du module
        return self::MODULEVS_SITECORE;
    }

    public function driverDB(): object {
        return $this->_driverDB;
    }

    function openCurInfo(string $nomCat, string $idGrp="", string $meta=""): bool {
    // ouverture un curseur
        $req = $this->privReqInfo($nomCat, $idGrp, $meta);

        utilDebug("req (def) =>", $req, false);
        return ($this->_driverDB)->openCur($req);
    }

    function openCurTable(string $table): bool {
    // ouverture un curseur sur la table donnée en paramétre
        return ($this->_driverDB)->openCur("SELECT * FROM {$this->_prefixe}$table");
    }

    function afficheInfo(bool $curseur, string $format, ...$champs): int {
    // Affiche les infos, retourne le nbr d'occurence
        $nbr=0;
        if (! $curseur)
            return $nbr;

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

    function renvoiInfo(bool $curseur, string $format, ...$champs): string {
    // renvoie les infos
        $ret="";

        if (! $curseur)
            return $ret;

        while ($info = ($this->_driverDB)->fetchCur($curseur)) {
            $tab = array();
            foreach ($champs as $valeur) {
                $tab[] = $info[$valeur];
            }
            $ret .=  sprintf($format, ...$tab);
            utilDebug("info  =>", $info, false);
        }
        ($this->_driverDB)->closeCur();
        return $ret;
    }
    

    function litInfo(string $nomCat, string $idGrp="", string $meta="") {
    // Lit un enregistrement dans la table info
        $req = $this->privReqInfo($nomCat, $idGrp, $meta);

        utilDebug("req (def) =>", $req, false);

        $info = ($this->_driverDB)->selectSQL($req);
        if ( ($this->_driverDB)->erreur ) 
            return false;

        return  $info;
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
                WHERE c.idCat = g.idCat AND g.idInfo = i.idInfo  AND i.boolActif = 'oui'
                        $whereGrp $whereCat $whereInfo
                ORDER BY nbrClassement ";

        utilDebug("req (def) =>", $req, true);
        $info = ($this->_driverDB)->selectSQL($req);
        if ( ($this->_driverDB)->erreur ) 
            return 0;

        utilDebug("ret req  =>", $info, true);
        return  $info["nbr"];
    }

    private function privReqInfo(string $nomCat, string $idGrp, string $meta): string {
        // function privée
        if ($nomCat !== "") 
            $whereCat = "AND c.nom = '$nomCat'";

        if ($meta !== "")
            $whereInfo = "AND i.meta = '$meta'";
        elseif ($idGrp !== "")
            $whereGrp = "AND g.idGrpItem = $idGrp";

        return "SELECT i.*  
                FROM {$this->_prefixe}categorie c, {$this->_prefixe}grpinfo g, {$this->_prefixe}info i 
                WHERE c.idCat = g.idCat AND g.idInfo = i.idInfo  AND i.boolActif = 1
                    $whereGrp $whereCat $whereInfo
                ORDER BY nbrClassement ";
    }

    private function privInitBase(): void {
    // creation de la base avec les tables si celle-ci sont vides
    // initialise avec les valeurs par défaut
        $data = new siteModeleData($this->_prefixe);

        foreach ($data->table as $table) {
            if ( ! ($this->_driverDB)->tableExiste("$table"))  {
                $req = $data->createTable($table);
                ($this->_driverDB)->executeSQL($req);
                for ($cpt = 0;  $req = $data->initTable($table, $cpt); $cpt++) {
                    ($this->_driverDB)->executeSQL($req);                        
                }
            }
        }
        unset($data);
    }

    private function privInitindex(string $lang = ""): bool {
    // On recherche la page index
    // alimente les properties titre et idMenu
        if ($lang !== "") 
            $ord_lang = "AND i.meta = 'lang=$lang' " ; 
        else
            $ord_lang = "";

        $req = "SELECT i.idGrpDown, i.titre, I.contenu 
            FROM {$this->_prefixe}categorie c, {$this->_prefixe}grpinfo g, {$this->_prefixe}info i
            WHERE c.idCat = g.idCat  AND g.idInfo = i.idInfo  AND i.boolActif = 1
            AND c.nom = 'site' 
            ${ord_lang}
            ORDER BY nbrClassement  ";

        $ret = ($this->_driverDB)->selectSQL($req);
        if (count($ret) > 0) {
            $this->titre   = $ret['titre'];
            $this->idMenu  = (int)$ret['idGrpDown'];
            return true;
        }

        return false;
    }

 }