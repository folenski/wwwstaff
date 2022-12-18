<?php

/**
 * Vue init, permet l'installation des tables et le chargemnt des donneés
 *
 * @author folenski
 * @since 1.0 17/12/2021: Version initiale 
 *
 */

use Staff\Config\Config;
use Staff\Databases\Table;
use Staff\Lib\Admin;
use Staff\Drivers\Sqlite;
use Staff\Models\Change;
use Staff\Models\DBParam;

function printHtml(?string $text = null, int $level = Admin::DISP_DEF)
{
    switch ($level) {
        case Admin::DISP_DANGER:
            $class = "text-danger";
            $icone = "<span class=\"glyphicon glyphicon-remove\" aria-hidden=\"true\"></span>";
            break;
        case Admin::DISP_DONE:
            $class = "text-success";
            $icone = "<span class=\"glyphicon glyphicon-ok\" aria-hidden=\"true\"></span>";
            break;
        case Admin::DISP_UPDATE:
            $class = "text-muted";
            $icone = "<span class=\"glyphicon glyphicon-plus\" aria-hidden=\"true\"></span>";
            break;
        default:
            $class = "text-info";
            $icone = "<span class=\"glyphicon glyphicon-console\" aria-hidden=\"true\"></span>";
            break;
    }
    if ($text === null) return;
    if ($level == Admin::DISP_ALERT)
        echo "<div class=\"alert alert-danger\" role=\"alert\">{$text}</div>";
    else echo "<p class=\"{$class}\">{$icone}</span>&nbsp;{$text}</p>";
}

try {
    $Adm = new Admin(sqldrv: new Sqlite());
    $Change = new Table(Entite: new Change(), prefixe: DBParam::$prefixe);
    $rows = $Adm->showTables();
    $nbrLu = count($rows);
    $nbrPrev = count(DBParam::TABLE);
    $baseDonnee = DBParam::$db;

    $dir = "{$root}/" . Config::REP_DATA;
    $files = array_filter(scandir($dir), function (string $el) {
        return str_ends_with($el, ".json");
    });
    $nbrFiles = count($files);
} catch (Exception $e) {
    die($e->getMessage());
}

/********************************************************************/
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@3.4.1/dist/css/bootstrap.min.css" integrity="sha384-HSMxcRTRxnN+Bdg0JdbxYKrThecOKuH5zCYotlSAcp1+c8xmyTe9GYg1l9a69psu" crossorigin="anonymous">
    <title>Start Staff</title>
</head>

<body>
    <div class="container">
        <div class="page-header">
            <h1><span class="glyphicon glyphicon-barcode" aria-hidden="true"></span> Staff<small>init</small></h1>
        </div>
        <div class="well">
            <?= "Environnement: {$Env->name}-{$baseDonnee} <br/>" ?>
            <?= "Nombre de tables: {$nbrLu} ({$nbrPrev} déclarées) <br/>" ?>
            <?= "Nombre de fichiers à charger: {$nbrFiles}" ?>
        </div>
        <div>
            <h3><span class="glyphicon glyphicon-import" aria-hidden="true"></h3>
            <div class="panel panel-default">
                <div class="panel-heading">Database</div>
                <div class="panel-body">
                    <?php $Adm->createAllTables(false, "printHtml"); ?>
                </div>
            </div>
            <?php foreach ($files as $file) : ?>
                <?php
                $dt = date("Y-m-d H:i:s", filemtime("{$dir}{$file}"));
                $updatedAt = "2000-01-01 00:00:00";
                $rows = $Change->get(id: ["lib" => "{$dir}{$file}", "version" => Config::VERSION]);
                if (count($rows ?? []) == 1) {
                    if ($rows[0]->updated_at <= $dt) {
                        $Change->put(
                            data: ["lib" => "{$dir}{$file}", "version" => Config::VERSION],
                            id: ["id" => $rows[0]->id]
                        );
                    } else {
                        $updatedAt = $rows[0]->updated_at;
                    }
                } else $ret = $Change->put(["lib" => "{$dir}{$file}", "version" => Config::VERSION]);
                ?>
                <h3><span class="glyphicon glyphicon-transfer" aria-hidden="true"></h3>

                <div class="panel panel-default">
                    <div class="panel-heading"><?= "{$file}" ?></div>
                    <div class="panel-body">
                        <?php if ($updatedAt <= $dt) : ?>
                            <?php $stat = $Adm->load("{$dir}{$file}", false, "printHtml"); ?>
                        <?php else : ?>
                            File modification time: <?= $dt ?>
                            <br />
                            Last data load: <?= $updatedAt ?>
                        <?php endif ?>
                    </div>
                </div>
                <?php if (($stat ?? null) !== null) : ?>
                    <div class="panel panel-success">
                        <div class="panel-heading">Summary</div>
                        <div class="panel-body">
                            <p><?= "Update: {$stat["upd"]}<br />Add: {$stat["add"]}<br />None: {$stat["exist"]}" ?></p>
                        </div>
                    </div>
                <?php endif ?>
            <?php endforeach ?>
        </div>
        <hr />
        <div>
            <h4> Staff </h4>
        </div>
    </div>
</body>

</html>