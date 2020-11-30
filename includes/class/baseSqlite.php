<?php
// Class pour faire un divers DB pour Sqlite3
// Dépendance : sqlite3 pour php7
// v1.0  - 07/11/2020 - mfe

class driverSqlite {
    var $est_vide;
    var $erreur; 
    var $prefixe;

    var $linkDB = null;   // champs provisoire
    private $_linkDB = null;

    function __construct(string $fichierDB, string $prefixeTable="") {
    // Constructeur de l'objet
        $this->prefixe = $prefixeTable . "_";
        $this->erreur = false;

        $path_parts = pathinfo($fichierDB);
        if (!file_exists($path_parts["dirname"])) {   // le repertoire de la base n'existe pas, on quitte
            $this->erreur = true;
            return;
        }        
        if (!file_exists($fichierDB))  // si le fichier de la base n'existe pas on l'indique 
            $this->est_vide = true;

        $this->_linkDB = new SQLite3($fichierDB);
        $this->linkDB  = $this->_linkDB;   // test provisoire

        if ($this->_linkDB === null)  
            $this->erreur = true;
    }

    function fin(): bool {
    // fermeture de la base 
        if ($this->_linkDB === null)  return false;

        ($this->_linkDB)->close();
        unset($this->_linkDB);
        return true;
    }

    function tableExiste(string $table): bool {
    // Vérif si la table existe
        $_ret=$this->selectSQL("SELECT name FROM sqlite_master WHERE type='table' and name = '{$this->prefixe}$table';");
        if  ($this->erreur)  
            return false;   
        else
            return  isset($_ret['name']);
    }

    function executeSQL(string $query): bool {
    // Execute le code SQL en parametre 
        if ( $this->_linkDB == null) return false;

        $_ret = ($this->_linkDB)->exec($query);

        if ((int)$_ret === 1)   // une erreur?
            $this->erreur = true;
        else
            $this->erreur = false;
        
        return $this->erreur;
    }

    function selectSQL(string $querySelect) {
    // une requete Select SQL et retourne la 1ere ligne 
        if ( $this->_linkDB == null) return false;

        $_ret = ($this->_linkDB)->querySingle($querySelect, true);
        if (is_array($_ret))   // une erreur?
            $this->erreur = false;
        else
            $this->erreur = true;

        return $_ret;
    }

    // fonctions de gestion des curseurs 
    function openCur(string $querySelect) {
        if ($this->_linkDB == null) return false;
        $_ret = ($this->_linkDB)->query($querySelect);

        if (is_object($_ret))   // une erreur?
            $this->erreur = false;
        else
            $this->erreur = true;

        return $_ret;
    }
    function fetchCur($curseur) {
        if ($this->erreur) return "";
        return $curseur->fetchArray();
    }
    function closeCur() {
        return true;
    }
    // fin des fonctions de gestion des curseurs
 }