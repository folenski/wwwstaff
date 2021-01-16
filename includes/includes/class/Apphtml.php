<?php 
/**
 * Class **Apphtml** Collection de fonctions pour générer du html 
 *
 * @author  Mario Ferraz
 * @since 1.0  11/11/2020 : version initiale
 * @since 1.1  08/01/2021 : transformation en classe statique
 *
 */

class Apphtml {

  const MODULEVS = "1.10";
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

  /**
   * Retourne la version du module.
   */
  public static function version(): string {
      return self::MODULEVS;
  }

  /**
   * Affiche un code html pour indiquer que la page n'a pas été trouvé. 
   * Utilise BootStap 
   */
  public static function pageNotFound(string $message, bool $type=true): string {
    return   '<div class="jumbotron shadow"><h1 class="display-4 text-center text-danger">'  
            . ( ($type) ? '<i class="fa fa-exclamation-triangle" aria-hidden="true"></i>' : '<i class="fa fa-plus-square" aria-hidden="true"></i>' )
            .  "&nbsp;$message</h1></div>";
  }

  /**
   * Affiche un message dans un div de la class alert 
   * Utilise BootStap 
   */

  public static function msg(string $msg, string $niveau = "info"): string {
  // affiche un msg dans une div html en bs4
    if ($msg !== "" ) {
      return  "<div class='container'><div class='alert alert-{$niveau} alert-dismissible'>" 
              . "<button type='button' class='close' data-dismiss='alert'>&times;</button>"
              . $msg  . "</div></div>";
    }
    return "";
  }

  /**
   * Fonction pour afficher le contenu d'une variable php 
   * utile pour en phase de développement
   */

  public static function debug(string $libelle, $obj, bool $trace = true): void {
    // affiche l'objet PHP
    if ($trace) {
      echo "<br><strong>$libelle</strong><pre>";
      print_r($obj);
      echo "</pre>";
    }
  }

  /**
   * permet d'affichier les liens vers le CSS ou le JS 
   * Les framework connus sont :
   * * materialize-1
   * * jquery-3.51
   * * animate
   * * bootstrap-4
   */
  public static function lien(string $framework, string $repLocal = ""): string {
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

  /**
   * Protege une entrée afin de l'ajouter en base de données 
   */
  public static function protected($strUnSecure, int $limit=0): string {
    if ($limit > 0)
      return substr (htmlspecialchars(strip_tags($strUnSecure)), 0, $limit);
    else
      return htmlspecialchars(strip_tags($strUnSecure));
  }
}