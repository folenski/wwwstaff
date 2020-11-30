<?php
// Class pour manipuler la base sqlite
// Dépendance : sqlite3 pour php7
// v1.0  - 07/11/2020 - mfe

require_once("config.php");
require_once(REP_INC . "classCreateBase.php");
require_once(REP_INC . "fonctUtile.php");

//$__modeTest_siteSqlite = true;

class siteSqlite {
    var $idConf = -1;
    var $titre;
    var $urlRW;
    var $langue;
    var $menuOrdre;
    var $prefixe;
    private $_linkDB = null;

    function __construct(string $fichierDB, string $prefixeTable, string $lang = 'fr') {
    // Constructeur de l'objet, Paramatre 
    // le fichier base de données sqlite, le prefixe des tables, la langue du site
        $this->prefixe = $prefixeTable;
        if (!file_exists($fichierDB)) {  // on regarde si la base existe, le répertoire doit exister
            $this->_linkDB = new SQLite3($fichierDB);
            $__initDB = new siteInitDB();
            $__initDB->CreateSQLSite("table", $__initDB->dbTables, $this->prefixe, $this);
            $__initDB->CreateSQLSite("ligne", $__initDB->dbinsertDef, $this->prefixe, $this);
            unset($__initDB);
        }
        else {
            $this->_linkDB = new SQLite3($fichierDB);
        }
        if ($this->_linkDB === null) return;

        // on recherche l'index par rapport à la langue de l'utilisateur
        $__result = ($this->_linkDB)->querySingle("SELECT * FROM {$this->prefixe}_conf WHERE langue = '{$lang}'", true);
        if (count($__result) === 0) {  // on prend la page par defaut
            $__result = ($this->_linkDB)->querySingle("SELECT * FROM {$this->prefixe}_conf WHERE defaut  = 1", true);
        }
        if (count($__result) !== 0) {
            $this->idConf    = (int)$__result["idConf"];
            $this->titre     = $__result["titre"];
            $this->urlRW     = (int)$__result["urlRW"];
            $this->langue    = $__result["langue"];
            $this->menuOrdre = (int)$__result["menuOrdre"];
        }
    }

    function afficheMenu(bool $Li=true, string $classA="", string $idActive="", string $classLi="", bool $SousMenu=false): string {
    // affiche un Menu et les sous-menus
    //  
        /* <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" data-toggle="dropdown" href="#">Dropdown</a>
        <div class="dropdown-menu">
        <a class="dropdown-item" href="#">Link 1</a>
        </div>
        </li> */
        $__retour = ""; 
        $__preMenu = "";  $__preMenuSM = "";
        $__postMenu = "</a>";  $__PostMenuSM = "";
        if ($this->idConf == -1)   return $__retour;

        if ($Li) {   // si on souhaite une menu avec des listes
            $__preMenu = "<li";
            $__preMenu .= ($classLi !== "") ? " class='{$classLi}'>" : ">";
            $__postMenu .= "</li>";
        }
        $__preMenu .= "<a";
        $__preMenu .= ($classA !== "") ? " class='{$classA}'" : "";
        $__preMenu .= " href='#";

        $__result = ($this->_linkDB)->query("SELECT * FROM {$this->prefixe}_menu WHERE idConf = {$this->idConf} AND ordre = {$this->menuOrdre} ORDER BY idMenu");
        while ($__ligne=$__result->fetchArray()) {
            // echo "\n deb {$__ligne['libelle']} ...";
            // echo "est vide " . empty($__ligne["sousMenu"]) . "\n";
            if (! empty($__ligne["sousMenu"]) && $SousMenu) { // existe-il un sous menu ?
                $__preMenuSM = str_replace($classLi, "{$classLi} dropdown",  $__preMenu);
                $__preMenuSM = str_replace($classA, "{$classA} dropdown-toggle",  $__preMenuSM);
                $__retour .= $__preMenuSM  . "' data-toggle='dropdown'> {$__ligne['libelle']}</a>";
                $__retour .= "<div class='dropdown-menu'>"; 
                $__savOrdre = $this->menuOrdre;
                $this->menuOrdre = $__ligne["sousMenu"];
                $__retour .=  $this->afficheMenu(false, "dropdown-item");
                $this->menuOrdre = $__savOrdre;
                $__retour .= "</div>";
                // $__retour .= "<p> toto:{$__ligne['libelle']}</p>";
            } 
            else {
                if ($idActive ==  "") { 
                    $__retour .= $__preMenu;
                }
                elseif (($idActive ==  "#") || ($idActive ==  $__ligne["idPage"])) { 
                    $__retour .= str_replace($classA, "{$classA} active",  $__preMenu);
                    $idActive =  "";
                }
                else {
                    $__retour .= $__preMenu;
                }  
                $__retour .= "{$__ligne["idPage"]}'>{$__ligne['libelle']}{$__postMenu}";
            }
        }
        return $__retour;
    }

    function litPages(string $class=""): string {
        $__retour = ""; 
        $__preDiv = "<div ";
        $__postDiv = "</div>";

        if ($this->idConf == -1) return $__retour;

        $__preDiv .= ($class !== "") ? "class='{$class}'" : "" ;
        $__preDiv .= " id='";

        $__result = ($this->_linkDB)->query("SELECT * FROM {$this->prefixe}_page WHERE idConf = {$this->idConf} ORDER BY ordre");
        while ($__ligne=$__result->fetchArray()) {
            $__retour .= "{$__preDiv}{$__ligne["idPage"]}'><h2>{$__ligne["titre"]}</h2>";
            $__retour .= "{$__ligne["contenu"]}{$__postDiv}";
        }
        return $__retour;
    }

    function table2Sql(string $table, string $fichier="") {
    // lit une table et la convertie en requete SQL 
        if ( $this->_linkDB == null) return false;

        ecritFichier("--TABLE {$this->prefixe}_{$table};", $fichier);
        ecritFichier("DELETE FROM {$this->prefixe}_{$table};", $fichier);
        $__result = ($this->_linkDB)->query("SELECT * FROM {$this->prefixe}_{$table}");
        while ($__ligne=$__result->fetchArray()) {
            $__nbrElem = count($__ligne)/2;
            $__out = "INSERT INTO {$this->prefixe}_{$table} VALUES (";
            for($__iii=0; $__iii < $__nbrElem; $__iii++) {
                    if (is_numeric($__ligne[$__iii])) {
                        $__out .=  $__ligne[$__iii];    
                    }
                    elseif (is_null($__ligne[$__iii])) {
                        $__out .=  "null";
                    }
                    else {
                        $__out .=   "'" . $__ligne[$__iii]  . "'";
                    }
                    if ($__iii != $__nbrElem - 1){
                        $__out .= ",";
                    }
            }
            $__out .=  ");";
            ecritFichier($__out, $fichier);
        }
        ecritFichier("--------------------------------", $fichier);
    }

    function sql2Table(string $fichier): bool {
    // lit un fichier SQL 
        echo "<br>Lecture du fichier $fichier" ;
        if ( $this->_linkDB == null) return false;
        if (!file_exists($fichier))  return false;
        echo "...ok";

        $__ope = "";
        $__handle=fopen($fichier, "r");
        while ($__fligne=fgets($__handle)) {
            $__fligne = trim($__fligne);
            // echo "<br> com" . substr($__fligne, 0, 2) . stripos($__fligne, "--");
            if (substr($__fligne, 0, 2) == "--")  continue;   // si commentaire on passe 
            if (substr($__fligne, -1) == ";") {
                $__ope .= " $__fligne";
                echo "<br>Maj ". substr($__ope, 0, 29) ." => ";
                if ( ($this->_linkDB)->exec($__ope) === true ) echo "...ok";
                else                                           echo "...<strong>ko</strong>";

                $__ope = "";
            }
            else {
                $__ope .= $__fligne;
            }
        }
        fclose($__handle);
        return true;
    }
    
    function fin(): bool {
    // fermeture de la base 
        if ($this->idConf == -1) {
            return false;
        }
        ($this->_linkDB)->close();
        $this->ifConf = -1;
        return true;
    }

    function execSQL(string $query): bool {
    // Fonction CallBack}
        if ( $this->_linkDB == null) return false;
        return ($this->_linkDB)->exec($query);
    }
}

if (isset($__modeTest_siteSqlite)) {
    echo "Mode test-------------------------------------\n";
    
    if (true)  { // test n°1 
        // ouverture en creation 
        $_fichDB = "/home/folenski/perso/wwwstaffkiev/sqldb/staffKiev.db";
        //echo "On efface le fichier {$_fichDB} \n";
        //@unlink($_fichDB) ;
        echo "On ouvre la base {$_fichDB} et creation des  tables ...\n";
        $_testDB = new siteSqlite($_fichDB, 'site', 'fr');

        echo "On affiche le menu par def ...\n";
        echo $_testDB->afficheMenu(true, "ClassA", "#",  "classLI",  true,);
        echo "\n";

    /*     echo "On affiche les pages par def ...\n";
        echo $_testDB->litPages("classDiv");
        echo "\n"; */
    }
    if (false) { // test n°2   -- JSON
        $_fichDB = "/home/folenski/perso/wwwstaffkiev/sqldb/base.db";
        $_testDB = new siteSqlite($_fichDB, 'site', 'fr');
        echo "On affiche table2Sql ...\n";
        echo $_testDB->table2Sql('page');
        echo "\n";
    }
    echo "Fermeture de la base ...\n";
    $_testDB->fin();
    unset($_testDB);
    echo "fin-------------------------------------------\n";
}