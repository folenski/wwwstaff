<?php

/**
 * Trait pour repondre si les méthodes http GET, POST, PUT, DELETE ne sont pas implementées 
 * 
 * @author  folenski
 * @version 1.0 10/12/2022: version initiale 
 *  
 */

namespace Staff\Api;

trait RestTrait
{
    function get(array $data, array $param, object $Env): array
    {
        return [
            "http" => RestInterface::HTTP_UNAVAIL,
            "errorcode" => RestInterface::ERR_INTERNAL,
            "response" => ["content" => "Not implemented"]
        ];
    }

    function post(array $data, array $param, object $Env): array
    {
        return [
            "http" => RestInterface::HTTP_UNAVAIL,
            "errorcode" => RestInterface::ERR_INTERNAL,
            "response" => ["content" => "Not implemented"]
        ];
    }

    function put(array $data, array $param, object $Env): array
    {
        return [
            "http" => RestInterface::HTTP_UNAVAIL,
            "errorcode" => RestInterface::ERR_INTERNAL,
            "response" => ["content" => "Not implemented"]
        ];
    }

    function delete(array $data, array $param, object $Env): array
    {
        return [
            "http" => RestInterface::HTTP_UNAVAIL,
            "errorcode" => RestInterface::ERR_INTERNAL,
            "response" => ["content" => "Not implemented"]
        ];
    }
}
