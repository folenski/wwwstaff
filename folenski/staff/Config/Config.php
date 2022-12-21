<?php

/**
 * Class Config : Configuration
 *
 * @version 1.0.0 12/12/2022: Version initiale
 * 
 */

namespace Staff\Config;

final class Config {
    const VERSION = 2;

    const REP_CONFIG = "/backend/config/";
    const REP_VIEWS = "/backend/views/";
    const REP_DATA = "/backend/data/";
    const REP_PUBLIC = "/public/";
    const REP_TMP = "/tmp/";
    const REP_SQLITE = "/sqldb/";
    const FILE_INI = "config.ini";
    const FILE_START = "/folenski/views/init.php";
    
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
