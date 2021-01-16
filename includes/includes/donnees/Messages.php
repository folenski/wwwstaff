<?php
/**
 * Gestion des messages pour les mails 
 *
 * @author  Mario Ferraz
 * @since 1.0  13/01/2021
 * @version 1.0.0 version initiale
 *
 */

$_messagedesc = [
    "id"          => "INTEGER PRIMARY KEY AUTOINCREMENT",
    "nom"         => "TEXT NOT NULL",
    "tel"         => "TEXT",
    "mail"        => "TEXT NOT NULL",
    "sujet"       => "TEXT",
    "message"     => "TEXT NOT NULL",
    "flag"        => "INTEGER DEFAULT 0",
    "dateheure"   => "TEXT NOT NULL"   
];

$TableMsg = new DbTable(PREFIXE_DB . "_message", $_messagedesc);
