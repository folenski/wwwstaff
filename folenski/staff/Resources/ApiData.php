<?php

/**
 * Gestion REST pour la lecture des données
 * 
 * @author folenski
 * @version 1.0 09/12/2022: version Intiale
 * @version 1.1 10/12/2022: ajout des champs ref et id_div
 * @version 1.2 13/08/2023, mise en place du swagger
 */

namespace Staff\Resources;

use Staff\Databases\Table;
use Staff\Models\DBParam;
use Staff\Lib\Carray;
use Staff\Models\Data;

final class ApiData implements RestInterface
{
    use RestTrait;

    /**
     * @OA\Get(
     *     path="/api/wwwdata/{ref}",
     *     description="Get a data",
     *     operationId="GetData",
     *     tags={"Get data"},
     *     @OA\Parameter(
     *        name="ref",
     *        in="path",
     *        description="ID",
     *        required=true,
     *        @OA\Schema(type="string")
     *     ),
     *     @OA\Response(response=200, description="Data object",
     *        @OA\JsonContent(type="array", @OA\Items(
     *           @OA\Property(property="ref", type="string", example="menu"),
     *           @OA\Property(property="id_div", type="string", example="tpl_menu"),
     *           @OA\Property(property="data", type="string", example="..." ),
     *         )),
     *     ),
     *     @OA\Response(response=400, description="Parameter missing", @OA\JsonContent(ref="#/components/schemas/controlsFailed")),
     *     @OA\Response(response=503, description="Internal error", @OA\JsonContent(ref="#/components/schemas/resourcesUnavail")),
     * )
     */
    function get(array $body, array $param, object $Env): array
    {
        $Data = new Table(DBParam::$prefixe, new Data());

        [$controle, $fails, $ref] = Carray::arrayCheck(
            $param,
            ["ref" => ["mandatory" => true, "protected" => true, "limit" => 100]]
        );
        if (!$controle) return $this->controlsFailed($fails);

        $rows = $Data->get(id: ["ref" => $ref]);
        if ($rows === false) return $this->resourcesUnavail();

        $bodyOut = [];
        foreach ($rows as $value) {
            array_push(
                $bodyOut,
                [
                    "ref" => $value->ref,
                    "id_div" => $value->id_div,
                    "data" => json_decode($value->j_content)
                ]
            );
        }
        return $this->retOk(data: $bodyOut);
    }
}
