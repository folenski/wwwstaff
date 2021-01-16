<?php
// Class pour l'administration d'un site
// v1.0  - 01/12/2020 - init
// v1.1  - 25/12/2020 - comptage du nombre d'utilisateurs à l'initialisation

define("MODULEVS_SITEADM", "1.10");

class siteCoreAdm {
    var $user;
    var $email;
    var $lastconnected;
    var $connect;
    var $countUsers;

    private $_driverDB;
    private $_prefixe;

    function __construct(object $driverDB) {
    // Constructeur de l'objet, Paramatre 
    // le fichier base de données sqlite, le prefixe des tables, la langue du site
        $this->_driverDB = $driverDB;
        $this->_prefixe = $driverDB->prefixe;
        $this->connect = false;

        // on verifie qu'il y a bien un utilisateur avec un mot de passe 
        $req = "SELECT count(1) as 'nbr'  FROM {$this->_prefixe}user WHERE pass is not null";
        $info = ($this->_driverDB)->selectSQL($req);
        if (($this->_driverDB)->erreur) 
            $this->countUsers = 0;
        else 
            $this->countUsers = $info["nbr"];
    }

    function fin() {
    // fermeture de la base 
        ($this->_driverDB)->fin();
        unset($this->_driverDB);
    }

    function version(): int {
    // retourne la version du module
        return MODULEVS_SITEADM;
    }

    function loginUser(string $email, string $pass = "", bool $verifPass = true): int {
    // Verif si l'utilisateur existe et controle le mot de passe 

        $req = "SELECT * FROM {$this->_prefixe}user  WHERE mail = '$email'";
        $info = ($this->_driverDB)->selectSQL($req);
        if (($this->_driverDB)->erreur) 
            return 1;  // erreur table 

        if (count($info) == 0)
            return 5;  // non connue

        if ( ! $verifPass ) // si on verif pas le mot de passe
            return 0;  // sucess

        if (!password_verify($pass, $info["pass"]))
            return 5;  // non connue

        // on met à jour la date de connection
        ($this->_driverDB)->executeSQL("UPDATE {$this->_prefixe}user SET dateMaj=datetime('now') WHERE mail = '$email'");
        
        $this->connect = true;
        $this->user = $info["nom"];
        $this->email = $info["mail"];
        $this->lastconnected = $info["dateMaj"];

        return 0; // success
    }

    function addUser(string $user, string $email, string $pass, string $level): int {
    // ajout d'un nouveau utilisateur

        // on doit verifier si l'email existe car c'est la clé
        $req = "SELECT 1 FROM {$this->_prefixe}user WHERE mail = '$email'";
        $info = ($this->_driverDB)->selectSQL($req);
        if (($this->_driverDB)->erreur) 
            return 1;  // erreur table 

        if (count($info) > 0)
            if ($info["nbr"] >= 1)
                return 3;  // mail existe

        if (strlen($pass) <= 8)
            return 4;

        utilDebug("pass  =>", $pass . "*", false);
        utilDebug("pass calculé =>", password_hash ($pass, PASSWORD_DEFAULT), false);

        $passcryt = password_hash($pass, PASSWORD_DEFAULT);

        // on insere l'utilisateur
        $req = "INSERT INTO {$this->_prefixe}user ('nom', 'mail', 'pass', 'nbrLevel' ,'dateCreat', 'dateMaj') VALUES ";
        $req .= "('$user', '$email', '$passcryt', $level,  datetime('now'), datetime('now'))";
        ($this->_driverDB)->executeSQL($req); 
        if (($this->_driverDB)->erreur) 
            return 1;  // erreur table 

        return 0; // success
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