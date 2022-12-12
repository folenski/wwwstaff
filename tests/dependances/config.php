<?php

use Staff\Databases\Database;
use Staff\Models\DBParam;

define("PREFIXE", "site_");
define("USER_SVC", "svc");
define("USER_SVC_PASS", "svc@12345678");

// initialisation de l'objet statique Database
Database::init(__DIR__ . "/devsetting.ini", __DIR__ .  "/");
DBParam::$prefixe = PREFIXE;
