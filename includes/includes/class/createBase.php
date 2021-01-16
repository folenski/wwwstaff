<?php
// Class nécessaure pour la création d'une base pour les sites staff
// v1.00 - 07/11/2020 - version initiale
// v1.05 - 25/12/2020 - ajout de la fonction initTable
// v1.10 - 10/01/2020 - ajout de la fonction initTable

class siteModeleData {

    const MODULEVS_MODELEDATA = "1.10";

    var $table   =  ["categorie", "info", "grpinfo", "user"];
    var $tableDesc= ["categorie" => ["idCat", "nom", "boolActif" ],
                     "info"      => ["idInfo", "idGrpDown", "boolActif", "titre", "meta", "contenu", "dateCreat", "dateMaj"],
                     "grpinfo"   => ["idGrpInfo", "idGrpItem", "idInfo",  "nbrClassement", "idCat"],
                     "user"      => ["idAutoUser", "nom", "mail" , "pass", "nbrLevel" ,"dateCreat", "dateMaj"]
                    ];
    var $tableCle = ["categorie" => "idCat",
                     "info"      => "idInfo",
                     "grpinfo"   => "idGrpInfo",
                     "user"      => "idAutoUser"
                    ];
    var $index;
    var $type     = ["SQLITE" => ["id" => "INTEGER", "nbr" => "INTEGER", "date" => "DATE", "bool" => "INTEGER", "defaut" => "TEXT"]
                    ];

    private $initTabuser = [];
    private $initTabcategorie = [["idCat" => 1, "nom" => "site", "boolActif" => 1]
                                ,["idCat" => 2, "nom" => "menu", "boolActif" => 1]
                                ,["idCat" => 3, "nom" => "page", "boolActif" => 1]
                                ,["idCat" => 4, "nom" => "article", "boolActif" => 1]];
    private $initTabinfo = [ 
     ["idInfo" => 1, "idGrpDown" => 10, "boolActif" => 1, "titre" => "DEMO", "meta" => "lang=fr", "dateCreat" => "<_f>datetime('now')", "dateMaj" => "<_f>datetime('now')"]
    ,["idInfo" => 2, "boolActif" => 1, "titre" => "HOME", "meta" => "home", "dateCreat" => "<_f>datetime('now')", "dateMaj" => "<_f>datetime('now')"]
    ,["idInfo" => 3, "boolActif" => 1, "titre" => "BIENVENUE", "meta" => "home",  "dateCreat" => "<_f>datetime('now')", "dateMaj" => "<_f>datetime('now')", 
       "contenu" => "<div class='container'> Bienvenue sur le site créé avec l'outil wwwstaff</div>" ]    
    ];
    private $initTabgrpinfo = [  ["idGrpInfo" => 1, "idGrpItem" => 1,  "idInfo" => 1, "idCat" => 1]
                                ,["idGrpInfo" => 2, "idGrpItem" => 10, "idInfo" => 2, "nbrClassement" => 1, "idCat" => 2]
                                ,["idGrpInfo" => 3, "idGrpItem" => 10, "idInfo" => 3, "nbrClassement" => 1, "idCat" => 3]
    ];

    private $_prefixe;
    private $_typeDB;

    function __construct(string $prefixe, string $typeDB = "SQLITE") {
    // creation, avec le prefixe des tables 
        $this->_typeDB = $typeDB;
        $this->_prefixe = $prefixe;
        if ( substr($this->_prefixe, -1) != "_" )   // on rajoute l'underscore si le prefixe n'en contient pas
            $this->_prefixe .= "_";
    }

    function fin() {
    // destruction
    // A completer, peut etre :)
    }
    
    public static function  version(): string {
    // retourne la version du module
        return self::MODULEVS_MODELEDATA;
    }

    function createTable(string $table): string {
    // donne la requete SQL pour la creation d'une table
        $tabDesc = ($this->tableDesc)[$table];  // on ne verifie pas que la table existe 
        $cle = ($this->tableCle)[$table];
        $sep = "";

        $preSql  = "CREATE TABLE {$this->_prefixe}$table (";
        foreach ($tabDesc as $champs) {
            $preSql .= "$sep $champs " . $this->champsType($champs);
            if ($champs == $cle) {   
                $preSql .= " PRIMARY KEY";
                if (substr($champs, 0, 6) == "idAuto")  // ajout de l'auto incrementation
                    $preSql .= " AUTOINCREMENT NOT NULL";
            }
            $sep = ",";
        }
        return "$preSql );";
    }

    function initTable(string $table, int $occurence): string {
    // initialise une table avec les valeurs par defaut
        switch ($table) {
            case "user" : $tab = $this->initTabuser; break;
            case "categorie" : $tab = $this->initTabcategorie; break;
            case "info" : $tab = $this->initTabinfo; break;
            case "grpinfo" : $tab = $this->initTabgrpinfo; break;
            default : return (string)null;
        }

        if ($occurence >= count($tab))
            return (string)null;

        // UtilDebug("insert ", $this->insertLigne($table, $tab[$occurence]), true);
        return (string)$this->insertLigne($table, $tab[$occurence]);
    }

    function insertLigne(string $table, array $tableCont): string {
    // Contruit la requete Insert 
            if (! isset(($this->tableDesc)[$table])) return ""; // la table existe ?
            $tabDesc = ($this->tableDesc)[$table];  
            $sep = "";
            $preSql  = "INSERT INTO {$this->_prefixe}$table (";

            // bcle pour la construction des champs 
            foreach ($tabDesc as $champs) {
                if (isset ($tableCont[$champs])) {
                    if (substr($champs, 0, 6) != "idAuto")   // si autoincrement , on laisse sqlite3 
                        $preSql .= "$sep $champs";
                }
                $sep = ",";
            }
            $preSql  .= ") VALUES ("; $sep = "";

            foreach ($tabDesc as $champs) {
                if (isset ($tableCont[$champs])) {
                    $preSql .= "$sep ". $this->champsValeur($champs, $tableCont[$champs]);
                }
                $sep = ",";
            }
            return "$preSql );";
    }

    function deleteLigne(string $table, array $tableCont): string {
    // Contruit la requete delete 
            if (! isset(($this->tableDesc)[$table])) return ""; // la table existe ?
            $tabDesc = ($this->tableDesc)[$table];  

            $preSql  = "DELETE FROM {$this->_prefixe}$table WHERE";

            // on cherche l'id , il doit etre dans les 1er champs 
            foreach ($tabDesc as $champs) {
                if (substr($champs,0, 2) == "id") {
                    $preSql .= " $champs = " . $this->champsValeur($champs, $tableCont[$champs]);
                    break;
                }
            }
            return "$preSql ;";
    }

    function champsType(string $nomChamps): string {
    // Retourne le type du champ 
        $_tc = ($this->type)[$this->_typeDB];
        
        foreach ($_tc as $_key => $_values) {
            if (substr($nomChamps,0, strlen($_key)) === $_key)
                return $_values;
        }

        return $_tc["defaut"];
    }

    function champsValeur(string $nomChamps, string $champsValeur): string {
    // Retourne la valeur du champs entre cote si nécessaire
        $_tc = ($this->type)[$this->_typeDB];

        foreach ($_tc as $_key => $_values) {
            if (substr($nomChamps,0, strlen($_key)) === $_key) {
                if (substr($champsValeur, 0, 4) == "<_f>")  // si c'est une fonction
                    return substr($champsValeur, 4);
                elseif (substr($nomChamps,0, 4) == "date") 
                    return  '"' . addslashes($champsValeur) . '"' ;
                else
                    return $champsValeur;
            }

        }

    // return  '"' . addslashes($champsValeur) . '"' ;
    return  '"' . $champsValeur . '"' ;
    }
}