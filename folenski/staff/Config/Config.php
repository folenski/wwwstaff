<?php

/**
 * Class Config : Configuration
 *
 * @version 1.0.0 12/12/2022: Version initiale
 * @version 1.0.1 13/07/2023: add route
 * @version 1.1.0 18/07/2023: add route pour les messages
 * 
 */

namespace Staff\Config;

final class Config
{
    const VERSION = 3;

    const REP_CONFIG = "/backend/config/";
    const REP_VIEWS = "/backend/views/";
    const REP_DATA = "/backend/data/";
    const REP_PUBLIC = "/public/";
    const REP_TMP = "/tmp/";
    const REP_SQLITE = "/sqldb/";
    const FILE_INI = "config.ini";
    const VIEWS_INIT = "folenski/views/init.php";
    const VIEWS_UNDER = "folenski/views/under.html";

    const ROUTE_WWW_START = ["GET", "/", "www@start", "www start"];
    const ROUTE_WWW_PROGRESS = ["GET", "[**:uri]", "www@progress", "www in progress"];
    const ROUTE_RELOAD = ["GET", "/reload", "link@", "link to reload data"];
    const ROUTE_API_MSG = ["POST", "/api/msg", "api@message", "apimsg"];
    const ROUTE_API_ENV = ["GET", "/api/wwwindex", "api@index", "apiindex"];
    const ROUTE_API_DATA =  ["GET", "/api/wwwdata/[**:ref]", "api@data", "apidata"];
    const ROUTE_API_ADMIN = [
        ["POST|DELETE", "/api/auth", "api@auth", "apiauth"],
        ["GET|POST", "/api/user", "api@user", "apiuser"],
        ["GET", "/api/msg", "api@message", "apimsg_2"],
        ["PUT|DELETE", "/api/msg/[**:id]", "api@message", "apimsg_id"],
        ["GET|DELETE|PUT", "/api/user/[**:name]", "api@user", "apiuser_name"],
        ["GET", "/api/log", "api@log", "apilog"]
    ];

    const PREFIXE_NAV = "@";

    const ENV_INDEX = [
        "lang" => "language",
        "nav" => "ref_nav",
        "start" => "ref_content",
        "entry" => "entry_file",
        "meta" => "meta",
        "title" => "title",
    ];
}
