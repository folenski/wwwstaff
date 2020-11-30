<?php
// script de création de la base 
// v1.00 - le 07/11/2020 - mfe

/*         echo "<br>";
        print_r ($tabDesc);
 */

class siteModeleData {
    var $table   =  ["categorie", "info", "grpinfo", "user"];
    var $tableDesc= ["categorie" => ["idCat", "nom", "actif" ],
                     "info"      => ["idInfo", "idGrpDown", "actif", "titre", "meta", "contenu", "dateCreat", "dateMaj"],
                     "grpinfo"   => ["idGrpInfo", "idGrpItem", "idInfo",  "nbrClassement", "idCat"],
                     "user"      => ["idUser", "nom", "mail" , "pass", "dateCreat", "dateMaj"]
                    ];
    var $tableCle = ["categorie" => "idCat",
                     "info"      => "idInfo",
                     "grpinfo"   => "idGrpInfo",
                     "user"      => "idUser"
                    ];
    var $index;
    var $type     = ["SQLITE" => ["id" => "INTEGER", "nbr" => "INTEGER", "date" => "DATE", "defaut" => "TEXT"]
                    ];
    private $_prefixe;
    private $_typeDB;

    function __construct(string $prefixe, string $typeDB = "SQLITE") {
    // creation, avec le prefixe des tables 
        $this->_prefixe = $prefixe . "_";
        $this->_typeDB = $typeDB;
    }

    function fin() {
    // destruction
    // A completer, peut etre :)
    }

    function createTable(string $table): string {
    // donne la requete SQL pour la creation d'une table
        $tabDesc = ($this->tableDesc)[$table];  // on ne verifie pas que la table existe 
        $cle = ($this->tableCle)[$table];
        $sep = "";

        $preSql  = "CREATE TABLE {$this->_prefixe}$table (";
        foreach ($tabDesc as $champs) {
            $preSql .= "$sep $champs " . $this->champsType($champs);
            if ($champs == $cle)
                $preSql .= " PRIMARY KEY";
            $sep = ",";
        }
        return "$preSql );";
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
    // Contruit la requete Insert 
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