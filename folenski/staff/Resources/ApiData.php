<?php

/**
 * Gestion REST pour la lecture des données
 * 
 * @author folenski
 * @version 1.0 09/12/2022: version Intiale
 * @version 1.1 10/12/2022: ajout des champs ref et id_div
 * @version 1.2 13/08/2023, mise en place du swagger
 * @version 1.3 21/04/2024, on retourne le tableau avec les objets j_content
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
     *     @OA\Response(response=200, description="Data j_content",
     *        @OA\JsonContent(
     *           @OA\Property(property="data", type="array", @OA\Items()),
     *         ),
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

        $rows = $Data->get(id: ["ref" => $ref], limit: 0);
        if ($rows === false) return $this->resourcesUnavail();

        if (count($rows) == 1) {
            return $this->retOk(data: ["data" => json_decode($rows[0]->j_content)]);
        }
        $bodyOut = [];
        foreach ($rows as $value) {
            array_push(
                $bodyOut,
                json_decode($value->j_content)
            );
        }
        return $this->retOk(data: ["data" => $bodyOut]);
    }
}
