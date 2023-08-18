<?php

/**
 * Gestion REST pour la lecture des Logs 
 * 
 * @author folenski
 * @version 1.0 11/07/2022, Version initialie
 * @version 1.1 09/12/2022, Utilisation d'un trait pour les methondes non implementées
 * 
 */

namespace Staff\Resources;

use Staff\Lib\Carray;
use Staff\Databases\Table;
use Staff\Models\DBParam;
use Staff\Models\Log;

final class ApiLog implements RestInterface
{
    use RestTrait;

    /**
     * 
     * @OA\Get(
     *     path="/api/log",
     *     description="Return a array of log",
     *     operationId="GetLog",
     *     tags={"Get Logs"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(response=200, description="Log object",
     *        @OA\JsonContent(type="array", @OA\Items(
     *           @OA\Property(property="id", type="integer"),
     *           @OA\Property(property="http_code", type="integer", example=200),
     *           @OA\Property(property="error_code", type="integer", example=0),
     *           @OA\Property(property="component", type="string", example="auth"),
     *           @OA\Property(property="message", type="string", example="{content=hello}"),
     *           @OA\Property(property="created_at", type="string", format="date-time"),
     *         )),
     *     ),
     *     @OA\Response(response=401, description="Security error", @OA\JsonContent(ref="#/components/schemas/GenericError")),
     *     @OA\Response(response=503, description="Internal error", @OA\JsonContent(ref="#/components/schemas/GenericError")),
     * )
     */
    function get(array $body, array $param, object $Env): array
    {
        $this->_method = HttpMethod::GET;
        $Log = new Table(DBParam::$prefixe, new Log());

        if (!$this->hasToken($param)) return $this->unauthorized();
        [$controle, $fails, $limit] = Carray::arrayCheck(
            $param,
            ["limit" => ["mandatory" => false, "protected" => true, "default" => 0]]
        );
        if (!$controle) return $this->controlsFailed($fails);

        $rows = $Log->get(limit: (int)$limit, order: $Log->orderBy(["id"]));
        if ($rows === false) return $this->resourcesUnavail();

        return $this->retOk(data: $rows);
    }
}
