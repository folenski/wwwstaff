<?php 
/*  
    Collection de foncitons pour générer du html
    le 11/11/2020  - v1.0 - mfe
    le 08/01/2021  - v1.1 - transformation en classe statique 
*/

class Apphtml {

  const MODULEVS_APPHTML="1.10";

  private static $list_fw = [
  "css_materialize-1" => 
  ["https://fonts.googleapis.com/icon?family=Material+Icons", "https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css"]
  ,"js_materialize-1" => 
  ["https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"]
  ,"css_animate" => 
  ["https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"]
  ,"css_bootstrap-4" =>
  ["https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css", "https://use.fontawesome.com/releases/v5.7.0/css/all.css"]
  ,"js_bootstrap-4" =>
  ["https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js"]
  ,"js_jquery-3.51" =>
  ["http://code.jquery.com/jquery-3.5.1.min.js"]
];

  private static $list_extra_tag = [
    "bootstrap.min.css" => 'integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous"' 
   ,"all.css" => 'integrity="sha384-lZN37f5QGtY3VHgisS14W3ExzMWZxybE1SJSEsQp9S+oqd12jhcu+A56Ebc1zFSJ" crossorigin="anonymous"' 
   ,"jquery-3.5.1.min.js" => 'integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0=" crossorigin="anonymous"'
   ,"bootstrap.min.js" => 'integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous"'
  ];

  public static function version(): string {
  // retourne la version du module
      return self::MODULEVS_APPHTML;
  }

  public static function pageNotFound(string $message, bool $type=true): string {
  // retourne un meesage d'erreur
    return   '<div class="jumbotron shadow"><h1 class="display-4 text-center text-danger">'  
            . ( ($type) ? '<i class="fa fa-exclamation-triangle" aria-hidden="true"></i>' : '<i class="fa fa-plus-square" aria-hidden="true"></i>' )
            .  "&nbsp;$message</h1></div>";
  }
    
  public static function msg(string $msg, string $niveau = "info"): string {
  // affiche un msg dans une div html en bs4
    if ($msg !== "" ) {
      return  "<div class='container'><div class='alert alert-{$niveau} alert-dismissible'>" 
              . "<button type='button' class='close' data-dismiss='alert'>&times;</button>"
              . $msg  . "</div></div>";
    }
    return "";
  }
  
  public static function debug(string $libelle, $obj, bool $trace = true): void {
    // affiche l'objet PHP
    if ($trace) {
      echo "<br><strong>$libelle</strong><pre>";
      print_r($obj);
      echo "</pre>";
    }
  }

  public static function lien(string $framework, string $repLocal = ""): string {
  // cette fonction retourne la directive css ou js correspondante au framework demandé
  // frameWork : materialize-1, jquery-3.51, animate, bootstrap-4
    $_ret = "";

    if (array_key_exists($framework, self::$list_fw)) {
      $_css = self::$list_fw[$framework];
      foreach ($_css as $_href) {
        $_fichier = basename($_href);

        if (array_key_exists($_fichier, self::$list_extra_tag)) 
          $_extraTab=" " . self::$list_extra_tag[$_fichier];
        else 
          $_extraTab="";

        if ( $repLocal != ""  && file_exists("{$repLocal}/{$_fichier}")) {
          $_extraTab="";
          $_href = "{$repLocal}/{$_fichier}";
        }
        if ( substr($framework, 0, 3) == "css")
          $_ret .= '<link rel="stylesheet" href="' . $_href . '"'  . $_extraTab . '>';
        else
          $_ret .= '<script src="'. $_href . '"'  . $_extraTab . '></script>' ;
      }
    } 
    return $_ret;
  }

  function protected($strUnSecure): string {
  // Permet de sécurisés une valeur en elevant les caractères dangereux
    $protected =  str_replace([';', ',', '\\', '&', '\'', '"'], "", $strUnSecure);  // supprime les caracteres problematiques
    return htmlentities($protected);

  }
}