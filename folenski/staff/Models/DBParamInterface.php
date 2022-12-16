<?php

/**
 * Constantes pour la définition des champs pour la base de données
 * 
 * @author  folenski
 * @since 1.0  04/08/2022: Version initiale 
 * @since 1.1  13/12/2022: Ajout des collate pour mysys 
 *  
 */

namespace Staff\Models;

interface DBParamInterface
{
    const SZ_SM_TXT = "256";
    const SZ_LG_TXT = "10000";
    const SZ_JSON = "10000";
    const COL_JSON = "CHARACTER SET utf8 COLLATE utf8_unicode_ci";
    const COL_TXT = "COLLATE latin1_general_ci";
}
