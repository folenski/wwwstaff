<?php

$_messagedesc = [
    "id"          => "INTEGER PRIMARY KEY AUTOINCREMENT",
    "name"        => "TEXT NOT NULL",
    "telephone"   => "TEXT",
    "mail"        => "TEXT NOT NULL",
    "sujet"       => "TEXT",
    "message"     => "TEXT",
    "dateheure"   => "TEXT NOT NULL"   
];

$TableMsg = new DbTable(PREFIXE_DB . "_message", $_messagedesc);