<?php
/**
 * Class **ApiResMsg**
 * Gestion des messages pour les mails pour l'API 
 *
 * @package includes\rest\Message.php
 * 
 * @author  Mario Ferraz
 * @since 1.0  14/01/2021 
 * @version 1.0.0 version initiale
 */

class ApiRestMsg extends ApiRest {

    public function maj(object $db, object $donnees): array {

        $champs = ["nom", "mail", "message", "dateheure"];
        
        if (! isset($donnees->nom, $donnees->mail, $donnees->message))
            return [["http" => 401], ["message" => "Erreur, des données sont manquantes"]] ;

        // valeurs obligatoires
        $nom     = Apphtml::protected($donnees->nom);
        $mail    = Apphtml::protected($donnees->mail);
        $message = Apphtml::protected($donnees->message);

        if (! filter_var($mail, FILTER_VALIDATE_EMAIL))
            return [["http" => 401], ["message" => "Erreur de format sur l'adresse mail"]] ;

        // valeurs facultatives
        if (isset ($donnees->tel)) {
            $tel     = Apphtml::protected($donnees->tel);
            array_push($champs, "tel");
        }
        if (isset ($donnees->sujet)) {
                $sujet   = Apphtml::protected($donnees->sujet);
                array_push($champs, "sujet");
        }
        // timestamps
        $dateheure = date("Y-m-d H:i:s");

        $_req = ($this->table)->insert(compact($champs));
        if ( $db->executeSQL($_req) ) {
            return [["http" => 200], ["message" => "Success"]];
        } 
        else {
            return [["http" => 500], 
            [["message" => "Erreur lors du traitement du mail"],["req" => "{$_req}"]]];

        }

    }
} 

$ApiMsg = new ApiRestMsg($TableMsg, "mail", ["POST"=> "OK"]);

// verif si l'api REST pour staff existe
if (isset($ApiStaff)) {
    $ApiStaff["mail"] = $ApiMsg;
}
