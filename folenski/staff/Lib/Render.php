<?php

/**
 * Class Render : pour generer du html
 * Utilisation du module LightnCandy pour générer le langage {{mustache}} 
 *
 * @author  folenski
 * @version 1.0 14/07/2022: Version Initiale 
 * @version 1.1 05/08/2022: Suppression de l héritage Table
 * @version 1.2 18/12/2022: Refactoring de la classe
 * 
 */

namespace Staff\Lib;

use Staff\Models\DBParam;
use Staff\Databases\Table;
use Staff\Models\Template;
use Staff\Models\Data;
use LightnCandy\LightnCandy;
use Staff\Config\Config;
use stdClass;

class Render
{
    public array $data = [];
    private array $_list_template;
    private string $_prefixe;

    /**
     * @param string $_internal_pref le prefixe interne utilisé dans la table data
     * @param string $_external_pref le prefixe qui devra remplacer le prefixe interne
     * @param string $_rep_out le répertoire où seront stockés les templates compilés
     */
    function __construct(
        private string $_rep_out,
        private string $_internal_pref = "@",
        private string $_external_pref = "",
    ) {
        $this->_prefixe = DBParam::$prefixe;
        $this->_compile($this->_rep_out);
    }

    /**
     * Produit le rendu html en fonction des templates présentes
     * @return array|false retourne un tableau indexé [ id_div => file_php ]
     */
    function render(): array|false
    {
        //$this->compile($rep_out);
        foreach ($this->data as $id_div => $val) {
            if (!array_key_exists($id_div, $this->_list_template))
                throw new \Exception("Key id_div {$id_div} not found");
                
            $renderer = include("{$this->_rep_out}/{$this->_list_template[$id_div]->file_php}");
            $render_out[$id_div] = $renderer($this->data[$id_div]);
        }
        return isset($render_out) ? $render_out : false;
    }

    /**
     * Mise en forme des données afin d'y appliquer plus tard un template.  
     * 
     * @param string $ref clé pour accèder la table "data" 
     * @param string $storekey clé pour le tableau interne où seront stockées les données
     * @return array|false tableau avec la liste des id de templates
     */
    function fetch_data(string $ref, string $storekey): array
    {
        $list_div = [];
        $Table = new Table(Entite: new Data(), prefixe: $this->_prefixe);

        if (($rows = $Table->get(
            ["ref" => $ref],
            limit: 0,
            order: $Table->orderBy(["id_div", "rank", "updated_at"], true)
        )) === false) return [];

        foreach ($rows as $Row) {
            $div = $Row->id_div;
            if (($decode = json_decode($Row->j_content, true)) === null) return null;
            if (array_key_first($decode) === 0) {
                $this->data[$div][$storekey] = $decode;
            } else {
                if (!isset($this->data[$div][$storekey]))
                    $this->data[$div][$storekey] = [];
                array_push($this->data[$div][$storekey], $decode);
            }
            array_push($list_div, $div);
        }
        return array_unique($list_div);
    }

    /**
     * Remplace le prefixe interne par le prefixe externe dans les liens
     * @param string $divkey clé sur de la template
     * @param string $storekey clé pour le tableau interne où seront stockées les données
     * 
     */
    function update_prefixe(string $divkey, string $storekey): void
    {
        if (!array_key_exists($divkey, $this->data)) return;
        if (!array_key_exists($storekey, $this->data[$divkey])) return;
        $this->_upd_prefixe($this->data[$divkey][$storekey], $this->_internal_pref, $this->_external_pref);
    }

    /**
     * Rend actif le premier élément (ajout de la propriété active) du menu et retourne cet élément
     * @param string $divkey clé de la template
     * @param string $storekey clé pour le tableau interne où seront stockées les données
     * @return object|false retourne l'élément du menu active
     */
    function set_active_first(string $divkey, string $storekey): object|false
    {
        if (!array_key_exists($divkey, $this->data)) return false;
        if (!array_key_exists($storekey, $this->data[$divkey])) return false;

        $key_nav = $this->data[$divkey][$storekey];
        $this->data[$divkey][$storekey][array_key_first($key_nav)]["active"] = true;
        return (object)$this->data[$divkey][$storekey][array_key_first($key_nav)];
    }

    /**
     * Rend l'élément actif qui répond à la condition $uri = element->uri
     * @param string $divkey clé de la template
     * @param string $storekey clé pour le tableau interne où seront stockées les données
     * @param string $uri l'élément à recherche
     * @return object|false retourne l'élément du menu active
     */
    function set_active_uri(string $divkey, string $storekey, string $uri): object|false
    {
        if (!array_key_exists($divkey, $this->data)) return false;
        if (!array_key_exists($storekey, $this->data[$divkey])) return false;
        return $this->_set_active_uri($this->data[$divkey][$storekey], $uri);
    }

    /**
     * Permet de trier en fonction du template
     * @param array $divs la référence de l'information
     * @param string $content
     */
    function sort(array $divs, string $content): void
    {
        foreach ($divs as $div) {
            if ($this->_list_template[$div]->order_by != 1) continue;
            krsort($this->data[$div][$content]);
        }
    }

    /** ----------------------------------------------------------------------------------------------
     *                                       P R I V E
     *  ----------------------------------------------------------------------------------------------
     */

    /**
     * Met à jour les prefixes internes contenus dans les données passées en paramétre 
     * @param array $data
     * @param string $in_pref
     * @param string $out_pref
     */
    private function _upd_prefixe(array &$data, string $in_pref, string $out_pref): void
    {
        $lenght = strlen($in_pref);
        foreach ($data as $key => $val) {
            if (array_key_exists("dropdown", $val))
                $this->_upd_prefixe($data[$key]["dropdown"], $in_pref, $out_pref);
            elseif (array_key_exists("uri", $val) && str_starts_with($val["uri"], $in_pref))
                $data[$key]["uri"] = $out_pref . substr($val["uri"], $lenght);
        }
    }

    /**
     * Recherche dans le tableau l'élément actif, => il est déterminé lorsque $uri = element.uri
     * l'élément peut avoir une propriété drownload pour indiquer qu'il comporte un sous menu qui doit être parcouru
     * @param array $data : les éléments
     * @param string $uri à rechercher
     * @return false|object retourne l'élément actif si il a été trouvé
     */
    private function _set_active_uri(array &$data, string $uri): false|object
    {
        foreach ($data as $key => $val) {
            if (array_key_exists("dropdown", $val)) {
                $item_sel = $this->_set_active_uri($data[$key]["dropdown"], $uri);
                if ($item_sel === false) continue;
                $data[$key]["active"] = true;
                return $item_sel;
            }
            if (array_key_exists("uri", $val) && array_key_exists("ref", $val)) {
                if (str_contains($val["uri"], $uri)) {
                    $data[$key]["active"] = true;
                    return (object)$data[$key];
                }
            }
        }
        return false;
    }

    /**
     * Compile tous les templates en utilisant LightnCandy (si le champs 'compile' = 1)
     * @param string $rep_out le répertoire où sont stockés les fichiers
     * @return bool faux en cas d'erreur
     */
    private function _compile(string $rep_out): bool
    {
        $Table = new Table(Entite: new Template(), prefixe: $this->_prefixe);

        $rows = $Table->get(limit: 0);
        foreach ($rows as $val) {
            $this->_list_template[$val->id_div] = $val;
            if ($val->compile != 1) continue;
            if ($val->file_php != "") {
                $phpStr = LightnCandy::compile(
                    $val->template,
                    array("flags" => LightnCandy::FLAG_NOESCAPE)
                );
                if (file_put_contents("{$rep_out}/{$val->file_php}", "<?php {$phpStr} ?>") === false)
                    return false;
                if ($Table->put(["compile" => 0], ["id_div" => $val->id_div]) === false)
                    return false;
            }
        }
        return true;
    }
}
