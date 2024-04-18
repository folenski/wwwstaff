<?php

/**
 * Gestion REST pour la lecture de l'index
 * 
 * @author folenski
 * @version 1.0 09/12/2022, Version Initiale
 * @version 1.1 10/12/2022, Ajout errorcode
 * @version 1.2 14/08/2023, Mise en place du swagger
 */

namespace Staff\Resources;

final class ApiIndex implements RestInterface
{
    use RestTrait;

    /**
     * @OA\Get(
     *     path="/api/wwwindex",
     *     description="Return all indexes",
     *     operationId="GetIndex",
     *     tags={"Get indexes"},
     *     @OA\Response(response=200, description="Array of index object",
     *        @OA\JsonContent(type="array",
     *           @OA\Items(
     *              required={"language", "uri", "ref_content", "title", "meta"},
     *              @OA\Property(property="language", type="string", example="uk"),
     *              @OA\Property(property="uri", type="string", example="root/"),
     *              @OA\Property(property="default", type="boolean"),
     *              @OA\Property(property="ref_nav", type="string", example="menu_uk"),
     *              @OA\Property(property="ref_content", type="string", example="home"),
     *              @OA\Property(property="entry_file", type="string", example="index.php"),
     *              @OA\Property(property="title", type="string", example="Blog about my life"),
     *              @OA\Property(property="meta", type="string", example="blog updated every week"),
     *           ),
     *         ),
     *     ),
     * )
     */
    function get(array $body, array $param, object $Env): array
    {
        if (($Env->Option->maintenance ?? true))
            return  $this->retOk(data: []);
        return $this->retOk(data: $Env->index);
    }
}
