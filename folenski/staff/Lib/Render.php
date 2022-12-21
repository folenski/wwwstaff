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

use Staff\Databases\Table;
use Staff\Models\Template;
use Staff\Models\Data;
use LightnCandy\LightnCandy;
use Staff\Config\Config;
use stdClass;

class Render
{
    public array $data = [];
    public array $uri = [];
    private array $_list_template;

    function __construct(
        private string $_prefixe,
        private string $_prefixe_uri,
        private string $_rep_out
    ) {
        $this->_compile($this->_rep_out);
    }


    /**
     * Produit le rendu html en fonction des templates présents
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
     * R&cuperation des données pour générer les templates en mustache, la données 
     * est recupéré par ka réf et on la classe dans un tableau interne avec la clé 
     *  $content
     * 
     * @param string $ref id de la données en table 
     * @param string $content nom de l'objet ayant les données du template. L'objet est alimenté
     * en interne
     * @return array|false la liste des templates
     */
    function fetch(string $ref, string $content): array|null
    {
        $list_div = [];
        $Table = new Table(Entite: new Data(), prefixe: $this->_prefixe);

        if (($rows = $Table->get(
            ["ref" => $ref],
            limit: 0,
            order: $Table->orderBy(["id_div", "rank", "updated_at"], true)
        )) === false) return false;

        foreach ($rows as $Row) {
            $div = $Row->id_div;
            if (($decode = json_decode($Row->j_content, true)) === null) return null;
            if (array_key_first($decode) === 0) {
                $this->data[$div][$content] = $decode;
            } else {
                if (!isset($this->data[$div][$content]))
                    $this->data[$div][$content] = [];
                array_push($this->data[$div][$content], $decode);
            }
            array_push($list_div, $div);
        }
        return array_unique($list_div);
    }

    /**
     * Dans le cas d'un menu, nous avons besoin de mettre les uri avec la prefixe de menu et 
     * on positonne un attribut active sur le menu qui match avec l'uri passée en paramétre
     *
     * @param array $divs les templates
     * @param string $content le nom de l'objet ayant les données pour le template
     * @param string $uri récupérer de l'url
     * 
     */
    function update_uri(array $divs, string $content, string $uri): void
    {
        foreach ($divs as $div) {
            if (array_key_exists($content, $this->data[$div])) {
                $noUri = ($uri == "") ? true : false;
                $this->_upd_uri($this->data[$div][$content], Config::PREFIXE_NAV . $uri, $noUri);
            }
        }
    }

    /**
     * @param string $uri pour la récuperation 
     * @return object|null Retourne l'objet donnée qui est associé à l'uri passée en paramétre
     */
    function get_metadata(string $uri): object|null
    {
        if (array_key_exists(Config::PREFIXE_NAV . $uri, $this->uri))
            return  $this->uri[Config::PREFIXE_NAV . $uri];
        return null;
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
     * Met à jour le menu avec le menu actif et un prefixe de menu
     * @param array $rows menu
     * @param string $uri
     * @param bool $noUri
     * @return bool si le menu actif a été trouvé
     */
    private function _upd_uri(array &$rows, string $uri, bool $noUri = false): bool
    {
        $active = false;
        $first = true;
        foreach ($rows as $key => $val) {
            if ($first &&  $noUri) $rows[$key]["active"] = true;
            $first = false;

            if (array_key_exists("dropdown", $val)) {
                if ($this->_upd_uri($rows[$key]["dropdown"], $uri))
                    $rows[$key]["active"] = true;
            }
            if (
                !array_key_exists("uri", $val) ||
                !str_starts_with($val["uri"], Config::PREFIXE_NAV)
            )
                continue;

            if ($val["uri"] == $uri)
                $rows[$key]["active"] = $active = true;
            $rows[$key]["uri"] = $this->_prefixe_uri . substr($val["uri"], 1);
            if (!array_key_exists($val["uri"], $this->uri)) {
                $this->uri[$val["uri"]] = new stdClass();
                $this->uri[$val["uri"]]->ref = $val["ref"] ?? "";
                $this->uri[$val["uri"]]->meta = $val["meta"] ?? "";
            } else {
                if ($this->uri[$val["uri"]]->meta == "")
                    $this->uri[$val["uri"]]->meta = $val["meta"] ?? "";
            }
        }
        return $active;
    }

    /**
     * Compile les templates avec la lib LightnCandy (champs compile = 1)
     * @param string $rep_out le répertoire où sont stockés les fichiers
     * @return bool faux si il y a eu une erreur 
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
