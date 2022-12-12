<?php

/**
 * Class Table : ajout les méthodes get, put, ...
 *
 * @author  folenski
 * @since 1.0  15/07/2022 : Version Initiale 
 * 
 */

namespace Staff\Databases;

use Staff\Services\Carray;

class Table extends SqlCore
{
    const RET_OK         = 0;
    const RET_DUP        = 1;
    const RET_MAJ        = 2;

    private array $_desc;
    private string $_name;
    private TableInterface $_Entite;

    function __construct(public string $prefixe, TableInterface $Entite)
    {
        $this->_Entite = $Entite;
        [$this->_name, $this->_desc] = $Entite->init();
        parent::__construct(
            _nom: $this->_name,
            _schema: $this->_desc,
            _prefixe: $prefixe,
            _simpleQuote: true
        );
    }

    /**
     * Compte les éléments dans la table
     * @param array|null $id, facultatif filtre pour la recherche
     * @return int|false false si une erreur est rencontrée, 
     *                   Retourne le nombre d'enregistrement 
     */
    public function count(?array $id = null): int|false
    {
        $this->select("count(*) as nbr");
        if ($id !== null) $this->_add_where($id);
        try {
            $req = Database::query($this->toStr())->fetch();
            return ($req  === false) ? false : $req->nbr;
        } catch (\Exception) {
            return false;
        }
    }

    /**
     * Efface la table
     * @param array|null $id, facultatif filtre pour la recherche
     * @return int|false false si erreur, sinon retourne le nombre d'enregistrement supprimé 
     */
    function del(?array $id = null): int|false
    {
        $this->delete();
        if ($id !== null) $this->_add_where($id);
        try {
            return Database::exec($this->toStr());
        } catch (\Exception) {
            return false;
        }
    }

    /**
     * Recherche dans la table 
     * 
     * @param array|null $id champs pour la recherche
     * @param int $limit si positionné à 0 pas de limite
     * @param array|null $join pour une jointure avec une autre table
     * @param array|null $order liste des champs pour ordonner les résultats
     * @param bool $asc vrai si on tri du plus petit ou plus grand
     * @return array rows|false
     */
    public function get(
        ?array $id = null,
        int $limit = 1,
        ?array $join = null,
        ?array $order = null,
    ): array|false {
        if ($id ===  null) {
            $this->select("*");
        } else {
            $this->select("*", true);
        }
        if ($join !== null) {
            $Table = $join[0];
            array_shift($join);
            $this->_add_join($Table, $join);
        }
        if ($id !==  null) $this->_add_where($id);

        if ($order !== null) {
            $asc = $order[0];
            array_shift($order);
            foreach ($order as $field) $order_format[$field] = "?";
            $this->order_by($order_format, $asc);
        }
        $this->limit($limit);

        try {
            if ($id ===  null) {
                $req = Database::query($this->toStr());
            } else {
                $req = Database::prepare($this->toStr());
                $req->execute($id);
            }
            if ($req === false) return [];
            return $req->fetchAll();
        } catch (\Exception) {
            return false;
        }
    }

    /**
     * doit etre utilisé avec la méthode get, permet de mettre une jointure avec 
     * une autre table  
     * 
     * @param TableInterface $Entite la table
     * @param array $id_join liste de champs sur la jointure
     * @return array le tableau à injecter dans le paramétre join
     */
    public function join(TableInterface $Entite, array $id_join): array
    {
        return [$Entite, ...$id_join];
    }

    /**
     * doit etre utilisé avec la méthode get, permet de mettre une jointure avec 
     * une autre table  
     * 
     * @param array $fields liste de champs 
     * @param bool $asc ordre du tri
     * @return array le tableau à injecter dans le paramétre order
     */
    public function orderBy(array $fields, bool $asc = false): array
    {
        return [$asc, ...$fields];
    }


    /**
     * Insérer ou met à jour un enregistrement
     * @param array $data les champs à modifier ou à stocker
     * @param array|null $id champs pour la recherche si positionée alors c'est une mise à jour
     * @return bool  
     */
    function put(array $data, ?array $id = null): bool
    {
        $created_at = $updated_at = date('Y-m-d H:i:s');

        if (key_exists("updated_at", $this->_desc))
            $data["updated_at"] = $updated_at;

        if ($id === null) {
            if (key_exists("created_at", $this->_desc))
                $data["created_at"] = $created_at;
            $this->insert($data, true);
            $prep = $data;
        } else {
            $this->update($data, true)->where($id);
            $prep = [...$data, ...$id];
        }
        try {
            $req = Database::prepare($this->toStr());
            $req->execute($prep);
        } catch (\Exception) {
            return false;
        }
        if ($req === false) return false;
        return true;
    }

    /**
     * Ajoute ou de met à jour uniquement si il est différent de l'enregistrement déjà présent
     * 
     * @throws string l'erreur
     * @param array $data les données à stocker, doivent être complètes 
     * @return array  le code suivant (RET_OK|RET_DUP|RET_MAJ) et la clé de l'element recu
     */
    function save(array $data): array
    {
        $element = "";
        if (($dataOk = $this->_Entite->check($data)) === false)
            throw new \Exception("Check Error : " . $this->_Entite->errors());

        $id = $this->_Entite->keys($data);
        foreach ($id as $kkk => $vvv) {
            $id[$kkk] = $dataOk[$kkk];
            $element .= "{$dataOk[$kkk]}, ";
        }
        $cle = substr($element, 0, -2);

        if (($req = $this->get($id)) === false)
            throw new \Exception("get {$cle} : SQL Error");

        if (count($req) == 0) {
            $req = $this->put($dataOk);
            if ($req === false)
                throw new \Exception("put {$cle} : SQL Error");

            return [self::RET_OK, $cle];
        }
        $enr = (array)$req[0];
        if (Carray::arrayCompare($dataOk, $enr)) {
            return [self::RET_DUP, $cle];
        } else {
            $this->put($dataOk, $id);
            return [self::RET_MAJ, $cle];
        }
    }

    /**
     * Construit la clause where à partir d'un tableau qui contient le couple (champs, $valeur)
     * la valeur peut contenir un opérateur, sinon par défaut c'est =
     * la clause where est ajoutée en local à cette class
     * @param array in/out $champs, retirer l'opérateur si il est trouvé
     */
    private function _add_where(array &$champs): void
    {
        $where = true;
        foreach ($champs as $key => $val) {
            $sep_val = explode(" ", $val, 2);
            if (count($sep_val) == 2 &&  $this->_is_operateur($sep_val[0])) {
                $op = $sep_val[0];
                $champs[$key] = $sep_val[1];
            } else {
                $op = "=";
            }
            if ($where) {
                $where = false;
                $this->where([$key => $champs[$key]], " {$op} ");
            } else $this->and([$key => $champs[$key]], " {$op} ");
        }
    }

    /**
     * Permet de contruire une inner join
     * @param TableInterface $Entite la table à joindre
     * @param array $id_join les champs pour la jointure 
     */
    private function _add_join(TableInterface $Entite, array $id_join = null): void
    {
        [$nom, $schema] = $Entite->init();
        $this->inner_join($nom, $id_join);
    }

    /**
     * @return bool, vrai c'est un opérateur SQL
     */
    private function _is_operateur(string $op): bool
    {
        return in_array($op, ["=", ">", "<", ">=", "<=", "<>", "like"]);
    }
}
