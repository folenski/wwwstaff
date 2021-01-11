<?php
// Class pour faire un divers DB pour Sqlite3
// Dépendance : sqlite3 pour php7
// 1.00  - 07/11/2020 - version initiale
// 1.10  - 26/12/2020 - correctif bug sur la fonction executeSQL
// 1.20 - 09/01/2021 - mise en place des exceptions pour gerer les erreurs

class DbSqlite {
    const MODULEVS_DBSQLITE = "1.20";

    protected $curseur = null;
    private $_linkDB = null;

    function __construct(string $fichierDB) {
    // Constructeur de l'objet
    // si il y a un probleme il leve une exception, mettre un bloc try

        $path_parts = pathinfo($fichierDB);
        if (!file_exists($path_parts["dirname"]))    // le repertoire de la base n'existe pas, on quitte
            throw new Exception("Erreur - Sqlite - Pb répertoire n'existe pas");

        $this->_linkDB = new SQLite3($fichierDB);

        if ($this->_linkDB === null)  
            throw new Exception("Erreur - Sqlite - Ouverture de la base" );

    }

    public function fin(): bool {
    // fermeture de la base 
        if ($this->_linkDB === null)  
            return false;

        ($this->_linkDB)->close();
        unset($this->_linkDB);
        return true;
    }

    public static function version(): string {
    // retourne la version du module
        return self::MODULEVS_DBSQLITE;
    }

    public function tableExiste(string $table): bool {
    // Vérif si la table existe
        $_ret=$this->selectSQL("SELECT name FROM sqlite_master WHERE type='table' and name = '{$table}';");
        return  isset($_ret['name']);
    }

    public function count(string $table): bool {
    // Compte les elements de la tables données
        $_ret=$this->selectSQL("SELECT count(1) as nbr FROM '{$table}' ;");
        return  $_ret['nbr'];
    }

    public function executeSQL(string $query): bool {
    // Execute le code SQL en parametre 
        if ( $this->_linkDB == null) 
            return false;

        return ($this->_linkDB)->exec($query);
    }

    public function selectSQL(string $querySelect): array {
    // une requete Select SQL et retourne la 1ere ligne
    // leve une exception en cas d'erreur 
        if ( $this->_linkDB == null) 
            return false;

        $_ret = ($this->_linkDB)->querySingle($querySelect, true);

        if (! is_array($_ret))   // une erreur?
            throw new Exception("Erreur - Sqlite - Pb requete SQL");

        return $_ret;
    }

    // fonctions de gestion des curseurs 
    public function openCur(string $querySelect, int $limit = -1, int $offset = 0): bool {
        if ($this->_linkDB == null) 
            return false;

        $this->closeCur(); // on ferme le curseur en cours

        if ( $limit > 0 ) 
            $querySelect .= " LIMIT {$limit} OFFSET $offset";

        $_ret = ($this->_linkDB)->query($querySelect);

        if (! is_object($_ret))   // une erreur?
            return false;

        $this->curseur = $_ret;
        return true;
    }

    public function fetchCur()  {  // retourne un boolean ou un tableau
        if ($this->curseur == null) 
            return [];
        return ($this->curseur)->fetchArray();
    }

    public function closeCur(): void {
        if ($this->curseur != null) {
            unset($this->curseur);
            $this->curseur = null;
        }
    }
    // fin des fonctions de gestion des curseurs
 }