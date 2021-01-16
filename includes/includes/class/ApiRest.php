<?php
/**
 * Class **ApiRest** 
 * Gestion des appels REST 
 *
 * @package includes\rest\Message.php
 * 
 * @author  Mario Ferraz
 * @since 1.0  13/01/2021 
 * @version 1.0.0 version initiale
 */

class ApiRest {

    const MODULEVS = "1.00";

    private   $services;
    protected $table;

    /**
     * Constructeur de la classe
     * 
     * @param object type DbTable  
     */

    function __construct(object $table, string $service, array $methodes) {
        $this->table = $table;
        $this->services[$service] =  $methodes;
    }

    /**
     * Retourne la version du module.
     */
    public static function  version(): string {
        return self::MODULEVS;
    }

    /**
     * Regarde en fonction du service demandé si la methode est autorisée pour le service demandé
     */
    public function estAutorise(string $service, string $methode): bool {
        if (array_key_exists($methode, $this->services[$service]))
                return true;
        
        return false;
    }
    
}

