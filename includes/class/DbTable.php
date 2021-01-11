<?php
// Class pour gerer la création du SQL pour une table
// v1.00 - 09/01/2021 - version initiale

class DbTable {

    const MODULEVS_DBTABLE = "1.00";

    private $nom;
    private $description;

    function __construct(string $nom, array $champs) {
    // Initialisation du nom de la table 
        $this->nom = $nom;
        $this->description = $champs;
    }

    function fin() {
    // destruction
    // A completer, peut etre :)
    }
    
    public static function  version(): string {
    // retourne la version du module
        return self::MODULEVS_DBTABLE;
    }

    public function create(): string {
    // retourne la requete SQL pour la creation d'une table
        $_sep = "";  
        $_sql = "CREATE TABLE {$this->nom} (";
        foreach ($this->description as $_key => $_value) {
            $_sql .= "{$_sep} {$_key} {$_value}";
            $_sep = ",";
        }
        return $_sql . ")" ;
    }

    public function drop(): string {
    // retourne la requete SQL pour la creation d'une table
        return "DROP TABLE {$this->nom}";
    }

    public function select(array $data, array $where, bool $prepare = false): string {
    // retourne la requete SQL pour la mise à jour dans une table
        $_sep = "";  
        $_sql = "SELECT ";
        if (count($data) == 0)
            $_sql .= "*";

        foreach ($data as $_key => $_value) {
            $_sql .= "{$_sep} {$_key}";
            $_sep = ",";
        }
        $_sep = "";
        $_sql .= " FROM {$this->nom} ";
        
        if (count($where) > 0)
            $_sql .= " WHERE ";

        $_sql .= $this->SQLlist ($where, " AND ", $prepare);
        return $_sql ;
    }
    
    public function insert(array $data, bool $prepare = false): string {
    // retourne la requete SQL pour l'insertion dans une table
        $_sep = "";  
        $_sql = "INSERT INTO TABLE {$this->nom} (";
        foreach ($data as $_key => $_value) {
            $_sql .= "{$_sep} {$_key}";
            $_sep = ",";
        }
        $_sep = "";
        $_sql .= ") VALUES (";

        $_sql .= $this->SQLlist($data, ",", $prepare);

        return $_sql . ")" ;
    }
    
    public function update(array $data, array $where, bool $prepare = false): string {
    // retourne la requete SQL pour la mise à jour dans une table
        $_sep = "";  
        $_sql = "UPDATE TABLE {$this->nom} SET ";
        foreach ($data as $_key => $_value) {
            $_sql .= "{$_sep} {$_key} = {$_value}";
            $_sep = ",";
        }
        $_sep = "";
        if (count($where) > 0)
            $_sql .= " WHERE ";

        $_sql .= $this->SQLlist ($where, " AND ", $prepare);


        return $_sql ;
    }

    public function delete(array $where, bool $prepare = false): string {
    // retourne la requete SQL pour la mise à jour dans une table
        $_sep = "";  
        $_sql = "DELETE TABLE {$this->nom} ";

        if (count($where) > 0)
            $_sql .= " WHERE ";

        $_sql .= $this->SQLlist ($where, " AND ", $prepare);
        
        return $_sql ;
    }

    private function SQLlist(array $data, string $sep, bool $prepare): string {
    // cette fonction permet de faire une liste pour les clauses SQL
        $_sep="";
        $_sql="";

        foreach ($data as $_key => $_value) {

            $_sql .= $_sep . ( ($sep == ",") ? "" : "{$_key}=" ) ;
            
            if ($prepare)
                $_sql .= $this->addQuote($_key, ":{$_key}");
            else
                $_sql .= $this->addQuote($_key, $_value);

            $_sep = $sep;
        }
        return $_sql;
    }

    private function addQuote(string $key, string $value): string {
    // par defaut on ajoute des quotes sauf pour les entiers doit etre enrichi
        if (array_key_exists($key, $this->description)) {
            // echo "\nthis " . $value . "*" .$this->description[$key] .  "*".  strripos($this->description[$key], "int") ;
            if ( strripos($this->description[$key], "int") !== false )
                return $value;
        }

        return "'{$value}'" ;
    } 

}