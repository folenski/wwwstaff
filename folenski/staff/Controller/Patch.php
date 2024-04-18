<?php

/**
 * class Patch : Applique une mise à jour logiciel sur le projet
 * @author folenski
 *
 * @version 1.0 17/04/2024: Version initiale
 */

namespace Staff\Controller;

use Staff\Config\Config;

class Patch
{

    /**
     * Apply a software update to the project.
     *
     * @param string $root The root directory where the update is to be applied.
     */
    static function apply(string $root): void
    {
        $rep_delivey = $root . Config::REP_DELIVERY;
        if (!is_dir($rep_delivey)) mkdir($rep_delivey);
        $rep_tmp = $root . Config::REP_TMP;
        if (!is_dir($rep_tmp)) mkdir($rep_tmp);

        $file = glob("{$rep_delivey}/*.zip");
        if (count($file) !== 1) {
            echo "<p class=\"text-danger\"> No patch found </p>";
            return;
        }
        $file_delivery = $file[0];
        $zip = new \ZipArchive();
        $res = $zip->open($file_delivery);
        if ($res !== TRUE) {
            echo "<p class=\"text-danger\">corrupted file {$file_delivery}</p>";
        }
        $zip->extractTo($rep_tmp);
        $zip->close();
        rename($file_delivery, "{$file_delivery}.done");

        $file = glob("{$rep_tmp}*.json");
        if (count($file) !== 1) {
            echo "<p class=\"text-danger\"> No json found </p>";
            return;
        }
        $json = json_decode(file_get_contents($file[0]), true);
        if ($json === null) {
            echo "<p class=\"text-danger\"> json {$file[0]} corrupted </p>";
            return;
        }
        if (isset($json["comment"])) {
            echo "<h2>Apply " . strtolower($json["comment"]) . "</h2>";
        } else {
            echo "<h2>Apply update</h2>";
        }

        foreach ($json["tasks"] as $task) {
            if (!array_key_exists("id", $task) || !array_key_exists("path", $task))
                continue;

            echo "<p> {$task["id"]} 🔜 [ {$task["path"]} ] </p>";

            $found = glob("{$rep_tmp}{$task["id"]}.zip");
            if (count($found) !== 1) {
                echo "<p class=\"text-danger\">corrupted file, no file found </p>";
                return;
            }
            $target = "{$root}{$task["path"]}";
            $target = "{$rep_tmp}{$task["path"]}";
            if (!is_dir($target)) mkdir($target);
            $file_delivery = $found[0];
            $zip = new \ZipArchive();
            $zip->open($file_delivery);
            $short_name = basename($file_delivery);
            echo "<p>💾 [{$short_name}] contains ", $zip->numFiles, " files </p>";
            $zip->extractTo($target);
            $zip->close();
        }
    }
}
